In this two-part series, my colleague Freek and I will discuss the architecture of a project we're working on. We will share some of our insights and answers to problems we encountered along the way. This part will be about the design of the system, while Freek's part will look at the concrete implementation.

Let's set the scene.

{{ ad:carbon }}

This project is one of the larger ones we've worked on. In the end it will serve hundreds of thousands of users, it'll handle large amounts of financial transactions, standalone tenant-specific installations need to be created on the fly.

One key requirement is that the product ordering flow — the core of the business — can be easily reported on, as well as tracked throughout history.

Besides this front-facing client process, there's also a complex admin panel to manage products. Within this context, there's little to no need for reporting or tracking history of the admin activities; the main goal here is to have an easy-to-use product management system. 

I hope you understand that I deliberately am keeping these terms a little vague because obviously this isn't an open-source project, though I think the concepts of "product management" and "orders" is clear enough for you to understand the design decisions we've made.

Let's first discuss the naive approach of how to design this system, it would be the approach discussed in my [Laravel beyond CRUD](*/laravel-beyond-crud) series.

In such system there would probably be two domain groups: `Product` and `Order`, and two applications making use of both these domains: an `AdminApplication` and a `CustomerApplication`. 

A simplified version would look something like this:

<div class="image-noborder"></div>

[![](/resources/img/blog/event-sourcing/es-1.png)](*/resources/img/blog/event-sourcing/es-1.png)

Having used this architecture successfully in previous projects, we could simply rely on it and call it a day. There are a few downsides with it though, specifically for this new project.

We have to keep in mind that reporting and historical tracking are key aspects of ordering products. We want to treat them as such in our code, and not as a mere side effect. 

For example: we could use our activity log package to keep track of "history messages" about what happened with an order. We could also start writing custom queries on the order and history tables to generate reports.

These solutions only work properly when they are minor side effects of the core business. In this case though, they are not. So Freek and I were tasked with figuring out a design for this project that made reporting and historical tracking an easy-to-maintain and easy-to-use, core part of the application.

Naturally we looked at event sourcing, a wonderful and flexible solution that fulfills the above requirements. Nothing comes for free though: event sourcing requires quite a lot of extra code to be written in order to do otherwise simple things. Where you'd normally have simple CRUD actions manipulating data in the database, you now have to worry about dispatching events, handling them with projectors and reactors, always keeping versioning in mind.   

While it was clear that an event sourced system would solve many of the problems, it would also introduce lots of overhead, even in places where it wouldn't add any value.

Here's what I mean with that: if we decide to event source the `Orders` module, which relies on data from the `Products` module, we also need to event source that one, because otherwise we could end up with an invalid state. If `Products` weren't event sourced, and one was deleted, we'd couldn't rebuild the `Orders` state anymore, since it's missing information.

So either we event source everything, or find a solution for this problem.

## Event source all the things?!

From playing around with event sourcing in some of our hobby projects, we were painfully aware that we shouldn't underestimate the complexity it adds. Furthermore, Greg Young stated that event sourcing a whole system is most often a bad idea. — he has a [whole talk](*https://www.youtube.com/watch?v=LDW0QWie21s) on misconceptions about event sourcing, it's worth a watch!

It was clear to us that we did not want to event source the whole application. It simply wouldn't make sense to do so. The only alternative left was to find a way to combine a stateful system, with an event sourced system. Surprisingly we couldn't find many resources on this topic. 

Nevertheless, we did some labour intensive research, and managed to find an answer to our question. The answer didn't come from the event sourcing community though, but rather from well-established DDD practices: bounded contexts.

If we wanted the `Products` module to be an independent, stateful system, we had to clearly respect the boundaries between `Products` and `Orders`. Instead of one monolithic application, we would have to treat these two modules as two separate contexts — separate services, which were only allowed to speak with each other in such a way so that could guarantee the `Orders` context would never end up in an invalid state.    

If the `Orders` service is built in such a way that it doesn't rely on the `Products` service directly, it wouldn't matter how that `Products` service is built.

When discussing this with Freek, I phrased it like this: think of `Products` as a separate service, accessed via a REST API. How would we guarantee our event sourced application would still work, even if the API goes offline, or makes changes to its data structure.

Obviously we wouldn't actually build an API to communicate between our services, since they would live within the same codebase on the same server. Still it was a good mindset to start designing the system.

The boundary would look like this, and each service has its own internal way of working.

<div class="image-noborder"></div>

[![](/resources/img/blog/event-sourcing/es-2.png)](*/resources/img/blog/event-sourcing/es-2.png)

If you read my Laravel beyond CRUD series, you're already familiar with how the `Products` service works. There's nothing special going on over there. The `Orders` context deserves a little more background information though.

## Event source some parts

So let's look at the event sourced part. I assume you that if you're reading this post, you have at least an interest in event sourcing, so I won't explain everything in detail.

The `OrderAggregateRoot` will keep track of everything that happens within this service. It will be the entry point for applications to talk with. It will dispatch events, which are stored and propagated to all reactors and projectors.

Reactors will handle side effects and will never be replayed, projectors will make projections, which in our case simple Laravel models. These models can be read from any other context, though they can only be written to from within projectors.

<div class="image-noborder"></div>

[![](/resources/img/blog/event-sourcing/es-3.png)](*/resources/img/blog/event-sourcing/es-3.png)

One design decision we made here was to not split our read and write models, for now we rely on a spoken _and_ written convention that these models are only written to via their projectors. One example of such a projection model would be an `Order`.

If there's one thing you need to remember, it's that the whole state of the `Orders` service should be able to be rebuilt only from its stored events.

So how do we pull in data from other contexts? How can the `Orders` context be notified when something happens within the `Products` context that's relevant for it? One thing is for sure: all relevant information regarding `Products`, will need to be stored as events within the `Orders` context; since within that context, events are the only source of truth. 

<div class="image-noborder"></div>

[![](/resources/img/blog/event-sourcing/es-4.png)](*/resources/img/blog/event-sourcing/es-4.png)

From the moment on events are stored within the `Orders` context, we' can safely forget about any dependency on the `Products` context. 

Some readers might think that we're duplicating data by copying events between these two contexts. We're of course storing an `Orders` specific event, based on when a `Product` was `created`, but there are more benefits to this than you might think.

First of all: the `Products` context doesn't need to know anything about what other contexts will be using its data. It doesn't have to take event versioning into account, because its events are never stored themselves. This allows us to work in the `Products` context as if it was any normal, stateful application, without the complexity event sourcing adds.

Second: there will be more than just the `Orders` context that's event sourced, and all of these contexts can individually listen to relevant events triggered within the `Products` context.

And third: we don't have to store a full copy of the original `Product` events, each context can cherry-pick and store the data that's relevant for its own use case.

## What about data migrations?

Say this system has been in production for over a year, and we decide to add a whole new event sourced context, which also requires knowledge about the `Products` context. Its original events weren't stored — because of the reasons listed above.

One question that presented itself was how we'd handle such a scenario. 

The answer is this: at the time of deployment, we'll have to read all product data, and send relevant events to the newly added context, based on the existing products. This one-time migration is an added cost, though it gives us the freedom to work within the `Products` context without ever having to worry about storing events. For this project that's a price worth paying.

## Final integration

Finally we're able to consume data in our applications, from within all contexts, using readonly models.

<div class="image-noborder"></div>

[![](/resources/img/blog/event-sourcing/es-5.png)](*/resources/img/blog/event-sourcing/es-5.png) 

Communication from applications to the `Products` context is done like any normal stateful application would do. Communication between applications and event sourced contexts such as `Orders` is done via its aggregate root. 

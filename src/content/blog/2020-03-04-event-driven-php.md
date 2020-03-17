Lately I've been tinkering with a unique kind of architecture for PHP applications. I want to tell you up front that I don't think it will solve any real-life problems soon; still I want to involve you in the thought-process. Who knows what kind of great ideas might arise?

In this post I'll go through the architecture step-by-step and address its benefits as well as its downsides — at least, the ones I can think of right now. I do have a proof-of-concept codebase open sourced, and I'll share insights from it throughout this post.

{{ ad:carbon }}

So, first things first, what the architecture is about. It's a long-running PHP server, with its entire state loaded in memory, built from stored events. In other words: it's event sourcing as we know it in PHP, but all aggregates and projections are loaded in-memory and never stored on disk.

Let's break it down!

## A long-running PHP server

The first pillar of this architecture is a long running server. The modern PHP landscape offers several battle-tested solutions for managing these kinds of processes: frameworks like ReactPHP, Amphp and Swoole allowed the PHP community to venture into another, unexplored world; while day-to-day PHP was most often related to its characterizing fast request/response cycle.

This fast request/response cycle is of course one of the things that made PHP great: you never had to worry about leaking state or keeping everything in sync: when a request comes in, a clean PHP process is started, and your application boots from 0. After the response is sent, the application gets completely destroyed.

I'm not proposing we ditch this battle-tested technique altogether; the fast request/response cycle is actually a critical part of the architecture I'll be describing. On the other hand: always booting the whole application from scratch has its downsides. 

In the architecture I'm describing, an application is split into two parts: one part is a regular PHP app, accepting HTTP requests and generating responses; while the other part is a behind-the-scenes backend server that's always running. A server that always has the whole application state loaded in memory, which allows the clients — our regular PHP apps — to connect with it, read data and store events.

Because the whole application state is always loaded in memory, you never need to perform database queries, spending resources on mapping data from the database to objects, or performance issues like circular references between ORM entities.

This sounds nice in theory, but we probably still need to be able to perform complex queries, something that databases are highly optimised for. It's clear that this architecture will require us to rethink certain aspects we're used to in regular PHP applications. I'll come back to this later.

First, let's look at the second pillar: event sourcing.

## Event sourcing 

Why would I suggest to make event sourcing part of the core of this architecture? You could very well have a long running server with all data loaded in-memory from a normal database.

Let's go down that road for a moment: say a client performs an update and sends it to the backend server. The server will need to store the data in the database, as well as refresh its in-memory state. Such systems will need to take care of updating the application state properly so that everything is correct after an update.
 
The most naive approach would be to perform the updates in the database and reload the whole application state, which in practice isn't possible due to performance issues. Another approach could be to keep track of everything that needs to happen when an update is received, and the most flexible way to do that is by using events.

If we're naturally leaning towards an event-driven system to keep the in-memory state synchronised, why then adding the overhead of storing everything in a database and needing an ORM to map the data back to objects? That's why event sourcing is the better approach: it solves all state syncing problems automatically, and offers a performance gain since you don't have to communicate with a database and work with an ORM.

What about complex queries though? How would you search, for example, a product store containing millions of items, when everything is loaded in memory. PHP doesn't particularly excel at these kinds of tasks. But again, event sourcing offers a solution: projections. You're perfectly able to make an optimised projection for a given task, and even store it in a database! This could be a lightweight in-memory SQLite database, or a full-blown MySQL or PostgreSQL server. 

Most important is that these databases aren't part of the application core anymore, they aren't the source of truth, they are useful tools living on the edge of the application's core. Very much comparable to building optimised search indices like ElasticSearch or Algolia. You can destroy these data sources at any point in time, and rebuild them from the stored events. 

That brings us to the final reason why event sourcing is such a great match for this architecture. When the server requires a reboot — because of a server crash or after a deploy — event sourcing offers you a way to rebuild the application's state much faster: snapshots.

In this architecture, a snapshot of the whole application state would be stored once or twice a day. It's a point where the server can be rebuilt from, without needing to replay all events.

As you can see, there are several benefits by building an event sourced system within this architecture. Now we're moving on to the last pillar: the clients.

## Clients

I've mentioned this before: with "clients" I mean server-side PHP applications communicating with the centralised backend server. They are normal PHP applications, only living a short time within the typical request/response cycle.

You can use whatever existing framework you want for these clients, as long as there's a way to use the event-server instead of directly communicating with eg. a database. Instead of using an ORM like Doctrine in Symfony or Eloquent in Laravel, you'd be using a small communication layer to communicate via sockets with the backend server.

Also keep in mind that the backend server and clients can share the same codebase, which means that from a developer's point of view, you don't need to worry about communication between a client and the server, it's done transparently.

Take the example of bank accounts with a balance. With this architecture, you'd write code like this:

```php
final class AccountsController
{
    public function index(): View
    {
        $accounts = <hljs type>Account</hljs>::<hljs prop>all</hljs>();

        return new <hljs type>View</hljs>('accounts.index', [
            'accounts' => $accounts,
        ]);
    }
}
``` 

Keep in mind that I mainly work in a Laravel context and I'm used to the Eloquent ORM. If you prefer to use a repository pattern, that's also fine.

Behind the scenes, `Account::all()` or `$accountRepository->all()` will not perform database queries, rather they will send a small message to the backend server, which will send the accounts, from memory, back to the client.

If we're making a change to the accounts balance, that's done like so:

```php
final class BalanceController
{
    public function increase(<hljs type>Account</hljs> $account, <hljs type>int</hljs> $amount): Redirect
    {
        $aggregateRoot = <hljs type>AccountAggregateRoot</hljs>::<hljs prop>find</hljs>($account);
   
        $aggregateRoot-><hljs prop>increaseBalance</hljs>($amount);

        return new <hljs type>Redirect</hljs>(
            [<hljs type>AccountsController</hljs>::class, 'index'], 
            [$account]
        );
    }
}
```

Behind the scenes, `AccountAggregateRoot::increaseBalance()` will send an event to the server, which will store it and notify all relevant subscribers.

If you're wondering what such an implementation of `AccountAggregateRoot` might look like, here's a simplified version:

```php
final class AccountAggregateRootRoot extends AggregateRoot
{
    public function increaseBalance(<hljs type>int</hljs> $amount): self
    {
        $this-><hljs prop>event</hljs>(new <hljs type>BalanceIncreased</hljs>($amount));

        return $this;
    }
}
```

And finally this is what the `Account` entity looks like, notice the lack of ORM-style configuration, these are simple in-memory PHP objects!

```php
final class Account extends <hljs type>Entity</hljs>
{
    public <hljs type>string</hljs> $uuid;

    public <hljs type>string</hljs> $name;

    public <hljs type>int</hljs> $balance = 0;
}
```

One final note to make: remember that I mentioned PHP's fast request/response cycle would actually be critical? Here's why: if we're sending updates to the server, we don't need to worry about broadcasting those updates back to the clients. Every client generally only lives for a second or two, so there's little to worry about keeping them in sync.

## The downsides

All of this sounds interesting in theory, but what about in practice? What about performance? How much RAM will you need to store everything in memory? Will we able to optimise reading the state by performing complex queries? How will snapshots be stored? What about versioning?

Lots of questions are still unanswered. The goal of this post was not to provide all answers, but rather share some thoughts and questions with you, the community. Who knows what you can come up with? 

I mentioned that the code for this is open source, you can take a look at it [over here](*https://github.com/spatie/event-server). I'm looking forward to hearing your feedback, on [Reddit](*https://www.reddit.com/r/PHP/comments/fk3qne/event_driven_application_server_in_php/?), via [Twitter](*https://twitter.com/brendt_gd) or [e-mail](mailto:brendt@stitcher.io).

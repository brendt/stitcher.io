I'm giving podcasting a try. Let me know what you think of it via [Twitter](*https://twitter.com/brendt_gd) or [e-mail](mailto:brendt@stitcher.io).

In this episode I talk about why I think final helps you write better maintainable code. 

<audio
    controls
    src="http://feeds.soundcloud.com/stream/617976093-brent-roose-826312539-01-solid-interfaces-and-final-rant-with-brent.mp3">
</audio>

You can download the episode [here](*http://feeds.soundcloud.com/stream/617976093-brent-roose-826312539-01-solid-interfaces-and-final-rant-with-brent.mp3) 
or listen in [iTunes](*https://podcasts.apple.com/be/podcast/rant-with-brent/id1462956030), [Stitcher](*https://www.stitcher.com/s?fid=403581&refid=stpr.) and [Spotify](*https://open.spotify.com/show/43sF0kY3BWepaO9CkLvVdJ?si=R-MIXaMHQbegQyq3gQm7Yw)

{{ ad:carbon }}

## Transcript

I just came back from the office, and we had the most interesting conversation. We talked about SOLID principles, interfaces — but most importantly, we talked about final.

This will probably a a rant with Brent, but it will be interesting.

First of all, let’s set the scene. My colleague Ruben and I were having a conversation about a package he’s building. It’s a little package to communicate to the Google timezone API — I didn’t even know the thing existed, but here goes.

This package provided one single class: the GoogleTimeZone class. It’s a simple class, a wrapper around Guzzle offering some convenience methods to the programmer to communicate with the API.

The goal of this package was to be used in a project, the GoogleTimeZone class would be injected into actions or services and be used over there.

Now this is where the problem arose: Ruben had written quite a lot of tests for code where this timezone API was used.

So before he knew, the test suite ran wild — there were tens, if not to say hundreds of requests to google — tests were running slow — google blocked us — it was a mess.

Recently at Spatie, we decided we wanted to use final more often. Many people ask us why: they don’t see much, if any, value in making classes final. They often think of inheritance as “flexibility”. Not being able to do so, means you’re simply making things more difficult. So let me explain the reasoning behind it, from our perspective: the package maintainers. I’ve got three reasons why to use final.

First of all, opening a package to “the outside world”, in other words: allow inheritance everywhere, means there’s a whole new area of backwards compatibility support we have to deal with. If every class can be inherited, and every method is public or protected; there’s no way of knowing who is using the package in what way.

Second, final ensures correct usage of our package. We, the package maintainers, provide a solution for a problem. The solution should be flexible, sure, but it also should be trustworthy. Say a programmer decides they want to override a method without realising that method is also used somewhere else in the package, they might introduce bugs without knowing it.

Lastly, we can make a good case for the use of final, in that it will make you write better code. To prove that though, we need to look back at the problem with our GoogleTimeZone class.
So my colleague Ruben asked me to come and take a look: here’s this final GoogleTimeZone class, used in tests. His problem is that there’s no way to mock it: because you cannot inherit from it, you also cannot provide another implementation.

The most simple solution would of course be to “de-finalise” the class: simply remove final in the package and be done with it. Strange though that we need to modify our package or project code in order for tests to be able to run.

Furthermore: final communicates… well, finality. If we’re removing final from the GoogleTimeZone class, we might communicate to the users of our package that they can inherit from this class: something the package isn’t meant to do.

Anyway: back to solving the mocking problem. We quickly came to the conclusion that if we want use final in this class, we also should provide an interface and program to that interface. This way we could make a mock implementing the interface, and our problems would be solved.

This raised another problem though: what exactly was provided by our class? It’s your average gateway class, wrapping around guzzle, providing authentication and provides a method per API endpoint which can be used within the codebase.

So if we want to provide an interface for this class, we’d have to define every single public API endpoint in it, and even worse: we’d have to mock every one of those methods. That’s rather complex: we started with one simple class and ended up with an out-of-proportions interface.

So this is where most developers who don’t want to use final stop, and just remove the final keyword. Instead of doing that, let’s back up to what we learned in school or college. Much of the theory we learned back then was rather boring, but it was also backed by tens of years experience of lots of programmers. They also encountered this problem and better yet: they already fixed it in the past.

So back to basics, back to the SOLID principles.

S stands for single responsibility. What about our class? Well.. It actually handles way more than one responsibilty: it handles API authentication, it sends HTTP requests and of course provides a method for each endpoint. Only one principle in and we’re already violating SOLID. 

Next up is open/closed, which is of course one of the reasons we added final: we want our code to be open for extension, but closed for modification. We want to prevent people of abusing the code provided by our package, but we also want to give them the flexibility to… make mocks. So while final addressed the “closed for modification” part, currently we’re missing the “open for extension” part.

L for the Liskov substitution principle: type safety. I wrote about it length at length on my blog. It’s less applicable on our case today, so let’s continue with Interface segregation. 

If we want to work with interfaces, we need to make sure these interfaces address the correct hot spots. We want interfaces to actually solve a problem, instead of just slapping them on it and call it “a solution”. They should be specific and small, which is something we really need to think about in solving our case.

The last one: dependency inversion, again less relevant to our situation, so I’m going to bypass it for now, and continue with the relevant principles for our case.

So starting with, what seemed like the two most important principles to tackle our problem: single responsibility and open/closed — how do we apply them?

Now, what makes me very happy is that these principles are known to the programming world for decennia, and still today they actually offer a real solution for our problem.

We have our class doing several things, and we actually only want to mock one a small part of this class: sending API requests. If we were to make an interface for the whole “GoogleTimeZone” class, we’d have to define every possible endpoint in this interface, and frankly, that’s a stupid solution.

So let’s split our big class into small pieces, each with a single responsibility. On the one hand, we have an interface for sending API requests, let’s call it our “gateway”. Now this interface has one, maybe two simple methods: on the one hand a method to authenticate with the Google API, and on the other hand a method to send a request and return a response. 

Now you might wonder: what are these requests? Well that’s our second interface. Let’s provide a simple class for every endpoint, each one contains the logic required to build the correct URL provide the HTTP verb, and prepare and provide the payload. These request classes may very well be final, because if you want to provide a new endpoint, you make a simple new class.

Now we could provide a third interface for responses, or could model responses as Data Transfer Objects, that’s a topic for another time.

So by focussing on making classes only have one responsibility, allowing the system to be open for extension, and providing small and specific interfaces, the whole “final” issue is gone. I’d even go as far as to say it’s thanks to final that we had to stop and think about our current implementation, and realise we were actually violating most basic principles.




By correctly implementing these principles:

- We created a system that’s easier to maintain, because it consists of small parts that are easy to understand 
- And second, it’s a system that’s open and user friendly for developers 

One closing remark: I would go as far as providing the mock — or at least one implementation of it — within the package, so to make it as easy as possible for developers using it.

In any case: I’m convinced that using final is that right decision here. You might not agree: and that’s fine. If that’s the case: feel to reach out, you can find me on twitter or send me an email. All information can be found on my website: stitcher.io

Thanks for listening, until next time.



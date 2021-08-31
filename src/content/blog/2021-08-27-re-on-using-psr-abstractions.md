Yesterday, I read Matthias Noback's excellent blog post on [PSR abstractions](*https://matthiasnoback.nl/2021/08/on-using-psr-abstractions/), and I'd like to share some thoughts on the topic as well.
I'm going to quote the parts I want to answer, but make sure the read the full post if you want more information about Matthias' point of view.

I want to make clear up front that I mean no disrespect to any individual and I've tried very hard to convey that in this post. If something however still comes across as disrespectful or hurtful, please [reach out to me](mailto:brendt@stitcher.io) to let me know and I'm happy to revisit it.

---

Let's start at the beginning.

> Several years ago, when the PHP-FIG created its first PSRs they started some big changes in the PHP ecosystem

They definitely did. I'd say the FIG has done an amazing job in modernizing the PHP ecosystem.

> PSRs for coding standards were defined, which I'm sure helped a lot of teams to leave coding standard discussions behind.

I think it's fair to say that most professional developers are using at least some PSR, with or without their knowledge.

> Next up were the PSRs that aimed for the big goal: framework interoperability. […] The idea […] was that frameworks could provide implementation packages for the proposed interfaces. So you could eventually use the Symfony router, the Zend container, a Laravel security component, and so on.

While framework interoperability is useful to some extent, it's unrealistic trying to make everything work together: you'd end up with a single framework in the end. There are only so many ways to implement a router or container, if there's one common set of interfaces shared among different frameworks then there'll be very little differences to the users of those frameworks. Sure there might be some implementation differences — but the goal of a framework is for users not to care about those, and to be able to focus on building applications instead. 

Matthias shares this concern: 

> One of the concerns I personally had about PSR abstractions is that once you have a good abstraction, you don't need multiple implementation packages

The beauty of the two or three (or four or five, depending how you count) major frameworks in our community is that they each have their unique way of tackling problems. Each framework has its own identity that attracts a different group of developers. 

Aiming for "full framework interoperability" is not only an unrealistic goal, but also something we simply don't need. 

But fair enough, maybe Matthias and the FIG aren't talking about _full_ framework interoperability, just about some parts. So let's talk about abstractions. Matthias says there's value in PSRs because they are tried and tested abstractions and you can trust them. 

Take, for example PSR-18, the HTTP client interface:

```php
interface ClientInterface
{
    /**
     * @throws <hljs type>\Psr\Http\Client\ClientExceptionInterface</hljs>
     */
    public function sendRequest(
        <hljs type>RequestInterface</hljs> $request
    ): ResponseInterface;
}
```

Granted, this is as simple as an HTTP client can get. It should be able to send a request and return a response. Matthias says the following about it:

> It is a great abstraction already: it does what you need, nothing more, nothing less. After all, what you need is to send an HTTP request and do something with the returned HTTP response

However, there's of course that infamous `<hljs type>RequestInterface</hljs>`. Here's Matthias again:

> The only problem about this interface is maybe: how can you create a RequestInterface instance?

I can resonate with that thought. Whenever I encounter a PSR-7 compliant library, I need to stop and think and search which package allows me to easily create — what I would think should be — a simple request object:

> Every time I need an HTTP client I struggle with this again: what packages to install, and how to get a hold of these objects?

Matthias contemplates the idea that maybe your own, simpler abstraction is a better option?

```php
interface HttpClient
{
    public function get(
        <hljs type>string</hljs> $uri, 
        <hljs type>array</hljs> $headers, 
        <hljs type>array</hljs> $query
    ): string;

    public function post(
        <hljs type>string</hljs> $uri, 
        <hljs type>array</hljs> $headers, 
        <hljs type>string</hljs> $body
    ): string;
}
```

"Unfortunately", he says:

> […] by creating my own abstraction I lose the benefits of using an established abstraction, being:
>
> 1. You don't have to design a good abstraction yourself.
> 2. You can use the interface and rely on an implementation package to provide a good implementation for it. If you find another package does a better job, it will be a very easy switch.

We're almost arriving at the core of my problem with the FIG these days. Sure, there's value in using tried and tested code, in not reinventing the wheel for every project. Matthias warns about the danger of doing that:

> If you wrap PSR interfaces with your own classes you lose these benefits. You may end up creating an abstraction that just isn't right, or one that requires a heavy implementation that can't be easily replaced.

The benefit of using PSRs in comparison to running your own implementation, is that your own implementation raises tons of questions that have been answered by the FIG before:

> - What is the structure of the $headers array: header name as key, header value as value? All strings?
> - Same for $query; but does it support array-like query parameters? Shouldn't the query be part of the URI?
> - Should $uri contain the server hostname as well?
> - What if we want to use other request methods than get or post?
> - What if we want to make a POST request without a body?
> - What if we want to add query parameters to a POST request?
> - How can we deal with failure? Do we always get a string? What kind of exceptions do these methods throw?

So why spending time and money on creating another abstraction while we already have one?

Well, have you considered the fact that… maybe the FIG doesn't always come up with the best abstractions? That maybe the process that takes months of discussion of a small group of developers, to finally come up with an interface that contains a few methods, might not actually solve the problems that developers are dealing with in real life?

Sure the question of creating HTTP requests and responses has been answered by the FIG. It's _a_ answer. Symfony and Laravel both have their own answer as well, simpler answers if you ask me. Better answers for my use cases. 

We've been talking about HTTP abstractions, but Matthias gives another example: the container interface. He agrees that the FIG doesn't always come up with an abstraction that's relevant to the community's needs:

> Without meaning to discredit the effort that went into it, nor anyone involved, there will always be standards that end up being outdated, like in my opinion PSR-11: Container interface.

Matthias carefully uses the word "outdated" here, though I want to say it's a plain irrelevant abstraction. The same way PSR-7 is irrelevant for most of the work I — and many others — are doing in Laravel or Symfony projects. I'm more than happy to ditch "interoperability" — what does that even mean when you're building a project closely tied to a framework and never intend to change it — and just use a simpler, straight forward, opinionated solution. It's also an abstraction, a good one, just not one that has an "official" name backed by the FIG.

Now some people tell me "you can't predict the future, maybe you _do_ want interoperability somewhere in the next years". I don't know about others but we actually outline the scope of projects in contracts with clients. They pay us to make an application specifically in one framework. There's no need for this level of interoperability.

Take a look, for example at both the container interface implementations of [Symfony](https://github.com/symfony/symfony/blob/5.4/src/Symfony/Component/DependencyInjection/ContainerInterface.php) and [Laravel](https://github.com/laravel/framework/blob/8.x/src/Illuminate/Contracts/Container/Container.php), both implement `<hljs type>\Psr\Container\ContainerInterface</hljs>`, and yet both add so many more methods. Implementing PSR-11 is merely a gimmick here for frameworks to be able to say "yes, we're PSR compliant"; because there's **no real interoperability between these two**.

Another example: PSR-7, the HTTP messages PSR. Both [Symfony](*https://github.com/symfony/symfony/blob/5.4/src/Symfony/Component/HttpFoundation/Request.php) and [Laravel](*https://github.com/laravel/framework/blob/8.x/src/Illuminate/Http/Request.php) don't implement PSR-7, because it simply doesn't solve their use cases. Oh and, just to be able to say "yes we're PSR compliant", there's the [PSR-7 bridge](*https://symfony.com/doc/current/components/psr7.html), which basically applies the adapter pattern.

Do you realise that applying the adapter pattern is **exactly the opposite** of what the FIG is trying to achieve? The FIG wants a common abstraction to be used across frameworks, while the [adapter pattern](https://en.wikipedia.org/wiki/Adapter_pattern) allows one interface to be used as _another_ interface.

---

I want to end with making a slight change to Matthias' last sentence:

> At the same time, we should also use PSR abstractions whenever it makes sense, since they will save us a lot of design work and will make our code less sensitive to changes in vendor packages.

I'd phrase it like this: "we should use **abstractions** whenever it makes sense". Whether it's the tried and tested Laravel or Symfony implementation of [the container](https://github.com/laravel/framework/blob/8.x/src/Illuminate/Contracts/Container/Container.php), [HTTP client](*https://github.com/symfony/contracts/blob/main/HttpClient/HttpClientInterface.php), [caching](https://github.com/symfony/contracts/tree/main/Cache), [queuing](https://github.com/laravel/framework/blob/8.x/src/Illuminate/Contracts/Bus/Dispatcher.php), … These implementations _work_. They are valid abstractions, even if they don't carry the "PSR" name.

Yes, we should use abstractions; but no, we shouldn't use irrelevant and outdated abstractions. It's not because an abstraction carries the name "PSR" that it's suddenly better than others. 

I figure there's a chance of some people getting angry by this post. You're allowed to, I’m open for that feedback.  Please reach out to me via [mail](mailto:brendt@stitcher.io) to tell me your thoughts. I don't question the sincerity and efforts of the FIG. I just genuinely believe they are trying to solve a problem that doesn't exist.

Let me end with how I started: the FIG has had a great impact on the PHP community, I'm very thankful for the early work they did as pioneers. The whole community needs to acknowledge that. I also think the FIG has reached its goal, and the project should be called complete.

---

As an addendum, I want to address one more point. I shared Matthias' original post on [/r/php](https://www.reddit.com/r/PHP/comments/pcituv/on_using_psr_abstractions_matthias_noback/) before publishing this one. There were some insightful discussions about it, and Matthieu Napoli, the author of PSR-11, [pitched in](https://www.reddit.com/r/PHP/comments/pcituv/on_using_psr_abstractions_matthias_noback/hajasv6/).

I want to address one of the things he said, because I reckon it might be a counterargument that people bring up after reading this post as well. He said:

> PSR-11 is great for libraries that want to interoperate with containers, for example:
> - Phinx to allow loading seed classes from your framework's container
> - Behat for doing dependency injection in feature contexts
> - Tactician command bus: load from your container (https://tactician.thephpleague.com/plugins/container/)
> - Faker for dealing with extensions
> - schmittjoh/serializer for lazy loading handlers from your container

In other words: I mainly look at PSRs from a framework's perspective, frameworks like Symfony or Laravel, while Matthieu is thinking about smaller, standalone, packages.

Here's one of the examples Matthieu gave in practice. Phinx optionally supports to set a container instance, which it'll use to [resolve seed classes](*https://github.com/cakephp/phinx/blob/master/src/Phinx/Migration/Manager.php#L888-L889).

```php
if ($this-><hljs prop>container</hljs> !== null) {
    $seed = $this-><hljs prop>container</hljs>-><hljs prop>get</hljs>($class);
}
```

And, fair enough: there seems to be some adoption at least in small, standalone packages. But what about the broader context? Are those features actually used by end users? Would it be worse if those package provided an adapter layer instead of relying on a third-party abstraction? There are a lot of _ifs_ here, and I'd like to [hear](mailto:brendt@stitcher.io) from users who actually have real-life experience with using these kinds of packages.

I mainly look at the FIG from a framework's point of view, I think that's relevant since it's the PHP **framework** interoperability group. I'm not entirely dismissing the merits of proper abstractions, I hope that was clear throughout this post.

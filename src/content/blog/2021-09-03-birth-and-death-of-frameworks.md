<div class="sidenote">
    <p>This post was first released on <a href="https://stitcher.io/mail">my newsletter</a>. Feel free to <a href="https://stitcher.io/mail">subscribe</a> if you want to be the first to see these kinds of posts, and want to talk about about them with me directly via email.</p>
</div>

Every once in a while, maybe every couple of years, someone has an idea that revolutionises the tech industry. All popular frameworks that we use day by day once started out as such a small and insignificant idea: React, TypeScript, Tailwind, Electron, Laravel.

It's hard to imagine a world without those frameworks. And yet: if you told me 15 years ago that the JavaScript ecosystem would be where it is today, I'd have a hard time believing you. 

---

Eric Evans once gave [a talk](*https://www.youtube.com/watch?v=T29WzvaPNc8) about trying to imagine alternatives to frameworks and libraries that we're used to today. He looked at a popular Java time library — Joda Time — and wondered if it was the best solution out there. It certainly was the most used one, but did that also mean "the best"? What followed was a thought experiment about dealing with time, thinking outside the box and solving domain problems from scratch without being influenced by prior knowledge and legacy expectations.

What if we'd start from a blank slate, Eric wondered?

I think that's exactly the spot where potential revolutionary ideas are born. What if we don't take our current solutions for granted but start from scratch instead?

I find Laravel to be a good example. You could easily say that, 10 years ago, the problem of "MVC frameworks for PHP" had already been solved by several frameworks; why would we need another one? And yet, Laravel managed to outgrow every other framework in just a few years time. There's quite a few data sources confirming that:

- [JetBrains' Developer Ecosystem Survey](*https://www.jetbrains.com/lp/devecosystem-2021/php/#PHP_which-php-frameworks-and-platforms-do-you-regularly-use-if-any)
- [StackOverflow's Developer Survey](*https://insights.stackoverflow.com/survey/2021#most-popular-technologies-webframe-prof)
- Packagist stats, comparing the relative growth of [Laravel](*https://packagist.org/packages/laravel/laravel/stats) and [Symfony](*https://packagist.org/packages/symfony/symfony/stats)

It's true that there's no single data set with a 100% accurate representation of the real world, though I think these sources are the most accurate available, and they all confirm the same: the massive growth of a framework that didn't really solve any new problems compared to its competitors, and yet skyrocketed in popularity.

Ironically though, it's that same popularity, that will most likely mean its end in the long run.

---

What I like about Eric's thought experiment, is that there weren't any alternative goals: he simply wanted to take an honest look at the software he had at hand and wonder "could it be better?".

There's a hidden caveat with Eric's approach though: he can come up with the best solution in the world, all the while ignoring legacy code and backwards compatibility. If you don't have to worry about an existing user base then yes, you _can_ come up with a better solution to almost any given problem.

Joda Time wasn't popular because it was _the best_, but because it grew with its users for many, many years. It was a trusted solution — sure it had its quirks, but it had proven itself more than enough.

The irony of managing popular software is that once it becomes popular, you can't promise the same quality you did at the start. You can't create the same disruption over and over again, because you need to accommodate your existing user base. In the end, you inevitably end up making compromises.

I feel like this is happening to Laravel today, 10 years after its birth. That's not a bad thing, by the way; it's a sign of maturity and stability, which are two key components in creating long lasting, valuable software.

On top of that, Laravel has solved most, if not all, of the problems there are when writing a web application. There aren't many more ground-breaking features that are missing. Every release brings some niceties, but nothing that's absolutely life critical. Meanwhile though, it takes a very long time to get up to speed with the [latest PHP additions](/blog/new-in-php-8), because there's a legacy user base to keep in mind. 

By satisfying their users and guaranteeing stability, any framework _must_ stop being as disruptive as they were at the start.
They _must_ create a void that will be filled by another framework somewhere in the future.

That's exactly why Laravel grew so popular: it filled the void created by other popular frameworks. Ten years ago, that void in the PHP community was simplicity and a low-level entry barrier. That's the void that Laravel filled, and turned out to be extremely successful.

Laravel is in the same place today, where other frameworks were a decade ago. There's a subtle void being created, and it'll grow steadily for the years to come. I believe there _will_ be a point in time where that void is large enough to spark the birth of a new framework. And while Laravel will keep being relevant for many more years — there's quite a lot of production code dependant on it — there will be another "best thing". Laravel, just like any other framework, will reach a tipping point; just like jQuery, Rails, Bootstrap, Symfony, Angular.

So the question at hand: who and what will fill that void?

## On the other hand…

I think it's possible for any framework to fill its own void, but it requires groundbreaking changes. In Laravel's case, I can come up with a few things I'd expect from a framework if it were created from scratch today:

- Proper type support (this is already partially [worked on](https://github.com/laravel/framework/pull/38538), though there's still lots of room for improvement).
- Getting rid of unnecessary technical debt. For example, Facades and other forms of magic: they served a purpose 10 years ago when its target audience wasn't using a proper IDE or relying on static analysis, but the PHP community is generally moving towards another type of programming.
- Embrace modern PHP features like [named arguments](/blog/php-8-named-arguments), [enums](/blog/php-enums) and [attributes](/blog/attributes-in-php-8).
- Venture in new territories like async and serverless, Laravel is actually already [doing](https://vapor.laravel.com/) [that](https://laravel.com/docs/8.x/octane), and is on the forefront in this area within the PHP community.

It'll be interesting to see whether Laravel will be able to fill its own void the next decade or so. I wouldn't be surprised if it did, or at least partially.

{{ cta:like }}

{{ cta:mail }}

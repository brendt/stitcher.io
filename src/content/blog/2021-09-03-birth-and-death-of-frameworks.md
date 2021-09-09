<script type="text/javascript" src="https://ssl.gstatic.com/trends_nrtr/2674_RC03/embed_loader.js"></script> 

Every once in a while, maybe every couple of years, there's an idea with the potential to revolutionise our tech world. All projects that we use day by day today once started out as such a small and insignificant idea: React, TypeScript, Rust, Electron, Laravel.

It's hard to imagine a world without those frameworks. And yet: if you told me 15 years ago that the JavaScript ecosystem would be where it is today, I'd have a hard time believing you. 

So what would the tech world look like 15 years from now? With the current speed that it's evolving, it's almost impossible not to think something big will happen in the next 15 years.

---

Eric Evans once gave [a talk](*https://www.youtube.com/watch?v=T29WzvaPNc8) a few years ago about trying to imagine alternative software to what we're used to today. He took a popular Java time library — Joda Time — and wondered if it was the best solution out there. It certainly was the most used one, but does that also mean "the best"? What follows is Eric's thought experiment about thinking outside the box, rethinking domain problems from scratch without being influenced by our prior knowledge and legacy expectations.

What if we'd start from a blank slate, Eric wondered?

I think that's exactly the spot where the potential revolutionary ideas are born. What if we don't take our current solutions for granted but start from scratch instead?

I find Laravel to be a good example. You could easily say that, 10 years ago, the problem of "MVC frameworks for PHP" had already been solved by several frameworks; why would we need another one? And yet, Laravel managed to outgrow every other framework in just a few years time. At least, according to every possible data source available.

Here's Laravel compared to other major PHP frameworks over the years on Google Trends:

<script type="text/javascript"> trends.embed.renderExploreWidget("TIMESERIES", {"comparisonItem":[{"keyword":"zend framework","geo":"","time":"all"},{"keyword":"/m/09t3sp","geo":"","time":"all"},{"keyword":"Laravel","geo":"","time":"all"},{"keyword":"/m/02qgdkj","geo":"","time":"all"},{"keyword":"/m/09cjcl","geo":"","time":"all"}],"category":5,"property":""}, {"exploreQuery":"cat=5&date=all&q=zend%20framework,%2Fm%2F09t3sp,Laravel,%2Fm%2F02qgdkj,%2Fm%2F09cjcl","guestPath":"https://trends.google.com:443/trends/embed/"}); </script>

Sidenote: don't mind the dip around September, 2020; Google made some changes to its ranking algorithms around that time; what's important are the relative changes over time. I've got some more sources for you to look at, all confirming the same trend:

- [JetBrains' Developer Ecosystem Survey](*https://www.jetbrains.com/lp/devecosystem-2021/php/#PHP_which-php-frameworks-and-platforms-do-you-regularly-use-if-any)
- [StackOverflow's Developer Survey](*https://insights.stackoverflow.com/survey/2021#most-popular-technologies-webframe-prof)
- Packagist stats, comparing the relative growth of [Laravel](*https://packagist.org/packages/laravel/laravel/stats) and [Symfony](*https://packagist.org/packages/symfony/symfony/stats)

It's true that there's no single data set with a 100% accurate representation of the real world, though I think all these sources confirm the same: the massive growth of a framework that didn't really solve any new problems compared to its competitors, and yet skyrocketed in popularity.

Ironically though, it's that same popularity, that will most likely mean its end in the long run.

---

What I liked about Eric's thought experiment was that there's wasn't any alternative goals: he simply wanted to take an honest look at the software he had at hand that day, and wonder "could it be better".

There's a hidden caveat with the approach though: Eric can come up with the best solution in the world, all the while ignoring legacy code and backwards compatibility. If you don't have to worry about an existing user base then yes, you _can_ come up with a better solution to any given problem.

Joda Time wasn't popular because it was _the best_, but because it grew with its users for many, many years. It was a trusted solution — sure it had its quirks, but it had proven itself more than enough.

That's the irony of managing popular software: once it becomes popular, you can't promise the same quality as you did at the start, because you'll always end up having to make some compromises somewhere, simply to be able to support your existing user base.

This is happening to Laravel today: it has solved most of the major problems there are to solve when writing a web application. There aren't that many ground-breaking features missing. Every release brings some niceties, but nothing that's absolutely life critical. Meanwhile it takes a long time to get up to speed with the [latest PHP additions](/blog/new-in-php-8), because there's a legacy user base to keep in mind.

From my point of view, Laravel has entered a phase of maturity. So what comes next? It looks like the strategy of Laravel's core team is to create products on top of their framework, and that seems like it's paying off quite well. But what after that?

There's already a small void being created right now, and it'll only grow very subtle for the years to come. But I believe there _will_ be a point in time where that void is large enough to spark the birth of a new framework. Laravel will of course keep being relevant for many more years, there's quite a lot of production code dependant on it. But it will reach a tipping point, just like every other framework has in the past.

So the question at hand: who and what will fill that void?

I'm looking forward to see what the future will bring.

{{ cta:like }}

{{ cta:mail }}

---

## Methodology

There's no one dataset that can accurately represents these worldwide trends while taking all variables into account. This is why we can only draw conclusions based on the relative changes we see within individual datasets, and compare those relative changes across datasets to validate whether they hold true.

You can refer to these pages if want to read more about JetBrains' and StackOverflow's methodology:

- [https://www.jetbrains.com/lp/devecosystem-2021/methodology/](*https://www.jetbrains.com/lp/devecosystem-2021/methodology/)
- [https://insights.stackoverflow.com/survey/2021#methodology](*https://insights.stackoverflow.com/survey/2021#methodology)

Furthermore, note that all google trends are scoped the same way:

- All time data
- Worldwide
- The Computers & Electronics category

Also note the consistent dip in Google Trends around September, 2020; this is due to changes made to Google's algorithm at that time.

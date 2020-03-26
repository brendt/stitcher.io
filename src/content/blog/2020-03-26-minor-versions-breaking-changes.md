When my colleague Sebastian [wrote](*https://sebastiandedeyne.com/composer-semver-and-underlying-dependency-changes/) about how bumping major versions isn't a breaking change, I would wholeheartedly agree.

I can't anymore. At least, not with how composer works today.

{{ ad:carbon }}

See yesterday, we stumbled upon a breaking change. And yet we didn't do any major version upgrades. Luckily another colleague of mine, Ruben, discovered the issue before pushing it to production.

Here's what happened.

One of our projects is now nearing its two-year anniversary mark, so suffice to say it has had its fair share of version bumps. After two years of development, these were some packages we used:

```json
{
    "<hljs keyword>laravel/framework</hljs>": "^6.5",
    "<hljs keyword>league/commonmark</hljs>": "^0.17.5",
    "<hljs keyword>spatie/laravel-view-components</hljs>": "^1.2",

    // …
}
```

First of all, `laravel/framework:^6.5`. We usually wait a month or two before updating to the next major Laravel version. As of today we're running 7 — which was needed to fix what went wrong.

Next there's `league/commonmark:^0.17.5`; a very specific dependency, added in May, 2018. At the time, this specific dependency was needed: according to the changelog it `Fixed incorrect version constant value (again)`. If we didn't use this version, it would conflict with other packages.

Two years went by, and `league/commonmark` has since tagged a first stable release. This is something they should have done way more early — but that's a topic for another day.

Finally there's `spatie/laravel-view-components:^1.2`. A package that has been archived recently, in favour of Laravel's blade components in version 7. Again: back in the day it made lots of sense to use this package. We might remove it at one point in the future, but this of course requires time, and costs money for our client. It isn't something we can "just do".

With the stage being set, it's time to look into the issue. Yesterday we ran a `composer update`, and things broke.

More specifically, our `spatie/laravel-view-components` package simply stopped working. Instead of rendering the view component, it only showed the render tag instead. It turned out it was a [known issue](*https://github.com/spatie/laravel-view-components/issues/21) as of version `1.3.0`, and [fixed](*https://github.com/spatie/laravel-view-components/pull/22) in `1.3.1`. This fix already existed when we ran our disastrous `composer update`, yet we never received it. Our `spatie/laravel-view-components` seemed to be locked on `1.3.0`, and didn't want to update to `1.3.1`.

Whenever you find yourself in such a pickle, don't panic and keep calm: composer can help you. Simply use `composer why-not spatie/laravel-view-components:1.3.1` and it will tell you exactly what's wrong.

It turned out that with `spatie/laravel-view-components:1.3.1`, its `laravel/framework` dependency version [was bumped](*https://github.com/spatie/laravel-view-components/commit/1ae57dcd9919de9019d30801cfb7dc2deea0cdbf) from `^6.0` to `^6.18`. 

That in itself shouldn't be a problem, we require `laravel/framework:^6.5` in our project, so we should be able to load `^6.18` just fine.

Unfortunately that didn't happen. You see, `laravel/framework` added a dependency on `league/commonmark:^1.1` in version `6.10`. In practice, this addition has the same effect as updating the major version of a dependency: from nothing to `^1.1`.

Again, that change in itself isn't a breaking change, yet it _did_ prevent `laravel/framework` in our project to update higher than `6.9`, because of our requirement on `league/commonmark:^0.17.5`. That in turn prevented `spatie/laravel-view-components` to update from `1.3.0` to `1.3.1`, which contained a much needed bugfix.

---

So who's to blame? Let's point the finger at myself first: we should have updated `league/commonmark` sooner. You could also say that combining a bugfix and a dependency version bump, like `spatie/laravel-view-components` did with `1.3.1`, should be avoided. Yet if the version bump is needed for a fix to work, there's little you can do.

You could say that `laravel/framework` shouldn't have updated one of its (implicit) dependencies, yet it's a perfectly normal thing to do, especially if the update fixes a security issue. 

The solution, by the way, consisted of updating `laravel/framework` to `^7.0` — we had to do this anyway sooner or later — and removing the `league/commonmark` dependency. So I don't think this change should be avoid by any open source vendors. Open source code should push us towards regular updates, I encourage that myself regularly.

The real problem though, is that composer never notified us that `laravel/framework` wasn't able to update further than `6.9` because of underlying conflicts. If you're not carefully managing each dependency, you're in danger of getting stuck on an outdated dependency, which might prevent much needed bug fixes to be installed.

As far as I know, there's no option the can be passed to `composer update` which can notify you about such situations and I think that would be a good future addition.

My colleague Freek pointed out that there is an external library that does exactly this: [https://github.com/Soullivaneuh/composer-versions-check](*https://github.com/Soullivaneuh/composer-versions-check). It'd be nice to have this functionality built-into composer.

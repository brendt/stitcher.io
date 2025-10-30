Last week, I shared [my wishlist for PHP in 2026](/blog/my-wishlist-for-php-in-2026), and the one item that stood out were "PHP Editions". Let's unpack why I think this would be a gamechanger for PHP.

## What are editions?

The word "edition" was pitched by Nikita Popov years ago, and was inspired by [Rust editions](https://doc.rust-lang.org/edition-guide/editions/index.html). Editions are a way to evolve a language, without being blocked by backwards compatibility problems:

> There are times when it's useful to make backwards-incompatible changes to the language […] Rust uses editions to solve this problem. When there are backwards-incompatible changes, they are pushed into the next edition. Since editions are opt-in, existing crates won't use the changes unless they explicitly migrate into the new edition.

Translating this idea to PHP could look something like this:

```php
namespace Tempest
{
    declare({:hl-property:edition:}=9_0);
}

namespace App
{
    declare({:hl-property:edition:}=experimental);
}
```

Of course there could be other syntax, but Nikita proposed to use `{php}declare` because it already exists in PHP. The proposal would rely on two main components:

- The ability to use `{php}declare` within a namespace, currently it's always scoped to the current PHP file; and
- The ability to toggle specific features within PHP's engine on or off based on these declared editions.

Because editions would be opt-in on a per-namespace level, you could introduce features in PHP that would break backwards compatibility, without affecting any existing code. In other words: vendor packages that don't have support for PHP 9.0's breaking feature X, would still run on PHP 9.0 without problems, because they haven't opted-into the new edition. Meanwhile, your project code could `{php}declare({:hl-property:edition:}=9_0)`, and you'd be able to use the newest features, without worrying about vendor dependencies breaking.

So that's the theory of editions. But…  

## Why break stuff?

"Can't we just… not break stuff?" Sure, we can; and PHP does its utmost best to keep backwards compatibility to the highest level. That doesn't always work out, and luckily, there are automated tools these days that have made upgrading PHP super easy. But still, based on [Packagist stats](/blog/php-version-stats-june-2025), we see around 50% of PHP projects run on outdated PHP versions. Around 50% of the most popular open source packages still have support for unsupported PHP versions as well. Last year, PHP [increased their security support](https://wiki.php.net/rfc/release_cycle_update) for outdated versions with an additional year, which — in my opinion — was the opposite of what should have happened, because now there's even less incentive for projects to upgrade.

So, circling back to "why break stuff?" Here are a couple of reasons:

- To fix oversights or long-standing bugs
- To improve performance
- To increase security
- To move PHP forward with modernized features

There's a group who say that PHP is good where it is, and we don't need any significant breaking changes anymore. In reality, though, data from several sources show that PHP's usage is stagnating at best, declining at worst. Modernizing the language has the potential to attract new developers and decrease churn. "Not breaking stuff" often stands in the way of doing so.

Here's what [Nikita Popov said](https://externals.io/message/106453#106454) on the matter when he was still working on PHP:
 
> I think that introducing this kind of concept for PHP is very, very important. We have a long list of issues that we cannot address due to backwards compatibility constraints and will never be able to address, on any timescale, without having the ability of opt-in migration.

From that same discussion, here's what [Mark Randall said](https://externals.io/message/106453#106459):

> The idea of PHP being held hostage to eternal backwards compatibility fills me with absolute dread.
> I've built most of my career on PHP, I find it a very powerful platform, but I find it lacking in some major areas. Some of those have reasonable workarounds(React, Swoole) and some of them do not (var level type enforcement, generics, universal annotations, first class functions and other symbols, union types, CoW classes etc).

Now imagine a world where old PHP code continued to work, without it being a blocker for the language to evolve? That's the potential of editions.

## It gets even better

Besides moving PHP forward by removing backwards compatibility concerns, there are two other things editions could provide.

First, there could be an "experimental" edition of PHP: it would contain features that are sure to be added in the language, but they might still change over time. Opt-in experimental features would remove the need to try to "get it perfect from the start" — something no one is able to do anyway. A good example is the new [URI extension](https://thephp.foundation/blog/2025/10/10/php-85-uri-extension/#thoughtfully-built-to-last):

> Thus, over the course of almost one year, more than 150 emails on the PHP Internals list were sent. Additionally, several off-list discussions in various chat rooms have been had.

I think a year-long discussion over a relatively small feature is a suboptimal use of time and talent. Furthermore there is still a chance of unforeseen things popping up after its release. Having spent years on open source projects myself, I continue to be amazed what kind of edge-cases people manage to come up to, ages after I thought the code was "finished". No one gets it right. Trying to do so is a fool's errand.

As a counter-example: in Tempest we introduced [experimental features](https://tempestphp.com/2.x/extra-topics/roadmap#experimental-features), and they've worked very well for us. It's our way of acknowledging that we won't get it perfect from the start and that we need the community's help. In an ideal world, people would use alpha and beta releases to help us find our shortcomings; but, in reality, they don't. Especially with a language like PHP where tooling support can take months after a stable release to be finalized, expecting people to spend their invaluable time being guinea pigs for alpha or beta testing just doesn't work out.

Editions would offer a solution where "features that need fine-tuning" could reach a much wider audience by ending up in stable releases but still being opt-in.

Then, the final potential benefit editions could bring is to sandbox features that might be too controversial. Yes, of course I'm talking about runtime-ignored generics. I think most people agree at this point that runtime type-checked generics aren't coming to PHP — which is ok because generics aren't a runtime-tool anyway. However, introducing dedicated syntax purely for the sake of static analysis is too controversial to some people. Editions could offer a solution here, so that only specific parts of your project would run in this "other" mode. It could even be a mode where all runtime type checks are disabled. Of course type information would still be available via reflection, but PHP wouldn't do any runtime check for you anyway. 

I actually think editions could be the key to get generics in PHP.
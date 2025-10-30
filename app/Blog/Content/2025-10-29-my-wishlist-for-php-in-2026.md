---
title: My wishlist for PHP in 2026
---

As we near the end of 2025, I thought it was a good time to reflect on my wishlist for PHP. I've done so a couple of times before, and I'm happy I've been able to scratch a couple of items off my list because they actually made it in the language by now:

- [Named parameters](/blog/php-8-named-arguments)
- [Improved type variance](/blog/new-in-php-74#improved-type-variance-rfc)
- [Enums](/blog/php-enums)
- [The pipe operator](/blog/pipe-operator-in-php-85)

But. There is more. Interestingly enough, my wishlist has been changing for the past year. Of course some of the same things are still on there (yes, generics, no suprise); but I've also let go of some things, and added others. Let's take a look!

By the way: if you have any features on your wishlist you want to share, remember to [leave them in the comments](#comments).

## PHP Editions

Because of PHP's backwards compatibility promise, new features added to the language have to be perfect: once they are added into the language, they are there to stay. That's one of the reasons features take so long to add and often end up full of compromise, because internals try to foresee all the edge cases. The most recent example is the new [URI extension](https://thephp.foundation/blog/2025/10/10/php-85-uri-extension/#thoughtfully-built-to-last):

> Thus, over the course of almost one year, more than 150 emails on the PHP Internals list were sent. Additionally, several off-list discussions in various chat rooms have been had.

Almost a whole year was spent on a relatively small feature. On top of that: no one is perfect, and things will have been overlooked.

In my opinion PHP would benefit tremendously from having opt-in features: a way to enable a specific feature only within a specific namespace. That way PHP could introduce new features that broke backwards compatibility, without actually affecting any existing code. It could reduce the need for "getting it perfect" if they had some kind of "experimental feature" opt-in as well: a stage where breaking changes would still be allowed. 

In other words: you could update to the latest PHP version, and only enable breaking changes for a part of your codebase (likely your own project code), but leave vendor code unaffected by it. Maybe the syntax would look something like this:

```php
namespace Tempest
{
    declare({:hl-property:edition:}=8_5);
}

namespace App
{
    declare({:hl-property:edition:}=experimental);
}
```

There is a lot to unpack about this topic, and I plan to write a followup-post about it soon. I do want to mention that I'm not pulling this idea out of thin air: Nikita actually proposed to add a concept of [PHP editions](https://externals.io/message/106453#106454), which in turn was based on [Rust Editions](https://doc.rust-lang.org/edition-guide/editions/index.html):

> I think that introducing this kind of concept for PHP is very, very important. We have a long list of issues that we cannot address due to backwards compatibility constraints and will never be able to address, on any timescale, without having the ability of opt-in migration.
> I do plan to create an RFC on this topic.

Unfortunately, Nikita has since left PHP, and that RFC never came to fruition.

## Interface default methods

Of all my wishlist items, I'm most hopeful this one might actually happen some day. There has already been [an RFC](https://wiki.php.net/rfc/interface-default-methods) for it in the past, and some internals have expressed interest in doing a second attempt at this one.

The idea is about interfaces providing a default implementation right within the interface:

```php
interface Request
{
    // …
    
    public array $query {
        get;
    }
    
    public function hasQuery(string $key): bool
    {
        return has_key($this->query, $key);
    }
}
```

Classes implementing this interface can override the default implementation, but they don't have to if they don't need to. This sounds a lot like abstract classes, doesn't it? Well, there are two differences:

- You can only inherit from one abstract class, while you can implement multiple interfaces
- Interfaces usually stand on their own, while deep inheritance chains with abstract classes is a much more common occurrence.

To me, the benefit of using interfaces over abstract classes lies in the fact that interfaces give a lot more freedom and flexibility.

## Generics

Oh… Generics 🥹. [What](/blog/php-generics-and-why-we-need-them) [can](https://www.youtube.com/watch?v=ffhhx5_TUB8) [I say](/blog/generics-in-php-1) [that](/blog/generics-in-php-video) [hasn't](/blog/generics-in-php-2) [been](/blog/generics-in-php-3) [said](/blog/generics-in-php-4) [before](/blog/the-case-for-transpiled-generics)? 

The good news is that the Foundation was still [looking into them](https://thephp.foundation/blog/2024/08/19/state-of-generics-and-collections/) this year. The bad news is that they came to the conclusion (again) that runtime generics are a bad idea. The better news is that generics conceptually are a compile-time tool so we don't even need runtime checks. The worst news is that considering runtime-ignored generics requires a mind shift within PHP that is unlikely to happen. 

But, who knows — maybe one day. I'll dream. We're getting close to Christmas, after all.

```php
$query = new ModelQuery<{:hl-generic:Post:}>;
```

## Structs

My final wishlist item (I'm keeping it pretty down-to-earth, don't you think?) is structs. I wrote a post about [my struggles with `private(set)` and `readonly`](/blog/readonly-or-private-set) and came to the conclusion that… much of my struggles would be solved if only PHP had simple structs…

```txt
{:hl-keyword:struct:} {:hl-type:Book:}
{
    {:hl-type:string:} {:hl-property:$title:};
    {:hl-type:Author:} {:hl-property:$author:};
    {:hl-type:ChapterCollection:} {:hl-property:$chapters:};
    {:hl-type:Publisher:} {:hl-property:$publisher:};
    {:hl-type:null|DateTimeImmutable:} {:hl-property:$publishedAt:} = {:hl-keyword:null:};
}
```

Am I asking too much? Maybe. Let me know [in the comments](#comments), and don't forget to leave your wishlist features as well!
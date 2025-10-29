---
title: My wishlist for PHP in 2026
---

As we near the end of 2025, it's a good time to reflect on my wishlist for PHP. I've done so in the past a couple of times, and what's really cool is that many features actually made it in the language by now:

- [Named parameters](/blog/php-8-named-arguments)
- [Improved type variance](/blog/new-in-php-74#improved-type-variance-rfc)
- [Enums](/blog/php-enums)
- [The pipe operator](/blog/pipe-operator-in-php-85)

But. There is more. Interestingly enough, my wishlist has been changing for the past year. Of course some of the same things are still on there (yes, generics, no suprise); but I've also let go of some things, and added others. Let's take a look!

By the way: if you have any features on your wishlist, don't forget to [leave them in the comments](#comments).

## PHP Editions

The way PHP is currently developed is that every new feature has to be perfect, because once it's added into the language, it's there to stay. That's one of the reasons features take so long to add and often end up full of compromise, because internals want to be able to account for all the edge cases. The most recent example is the new [URI extension](https://thephp.foundation/blog/2025/10/10/php-85-uri-extension/#thoughtfully-built-to-last):

> Thus, over the course of almost one year, more than 150 emails on the PHP Internals list were sent. Additionally, several off-list discussions in various chat rooms have been had.

Almost a whole year was spent on a relatively small feature. On top of that: no one is perfect, assuming that it will actually be perfect would be foolish.

In my opinion — and again, I'll dig deeper into this in the coming weeks — PHP would benefit tremendously from having opt-in features (maybe even some opt-in experimental features as well). There is so much to say about the topic, but I'll keep that for another day. I do want to mention in closing that I'm not pulling this idea out of thin air: Nikita actually proposed to add a concept of ["PHP editions"](https://externals.io/message/106453#106454):

> I think that introducing this kind of concept for PHP is very, very important. We have a long list of issues that we cannot address due to backwards compatibility constraints and will never be able to address, on any timescale, without having the ability of opt-in migration.
> I do plan to create an RFC on this topic.

Unfortunately, Nikita has since left PHP, and that RFC never came to fruition

## Interface default methods

Of all my wishlist items, I'm most hopeful this one might actually happen some day. There has already been [an RFC](https://wiki.php.net/rfc/interface-default-methods) for it in the past, and if memory serves me right, they wanted to do a second attempt at this one.

Explaining why I want interface default methods warrants a [dedicated blog post](http://stitcher.io.test/blog/extends-vs-implements) (or maybe [two](http://stitcher.io.test/blog/is-a-or-acts-as)), but the tl;dr is that modern languages such as Go and Rust have a different take on inheritance, which I personally like a lot more. Interface default methods would be a way to mimic at least part of that behaviour in PHP, and I would be so up for it.

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

If you really want to dive deep into the topic, I'd suggest reading [this post](https://lwn.net/Articles/548560). It requires some time, but was mind-blowing to me how much it clicked.

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
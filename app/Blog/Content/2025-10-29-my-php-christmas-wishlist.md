---
title: My PHP 2025 christmas wishlist
---

As we near the end of 2025, it's a good time to reflect on my wishlist for PHP. I've done so in the past a couple of times, and what's really cool is that many features actually made it in the language by now:

- [Named parameters](/blog/php-8-named-arguments)
- [Improved type variance](/blog/new-in-php-74#improved-type-variance-rfc)
- [Enums](/blog/php-enums)
- [The pipe operator](/blog/pipe-operator-in-php-85)

But. There is more. Here's what I wish would be added.

By the way: if you have any features on your wishlist, don't forget to [leave them in the comments](#comments).

## Experimental features

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

The good news is that the Foundation was still [looking into them](https://thephp.foundation/blog/2024/08/19/state-of-generics-and-collections/) this year. The bad news is that they came to the conclusion (again) that runtime generics are a bad idea. The better news is that generics conceptually are a compile-time tool so we don't even need runtime checks. The worst news is that such a mind shift within PHP is unlikely to happen. 

But, who knows — maybe one day. I'll dream. It's Christmas, after all.

```php
$query = new ModelQuery<{:hl-generic:Post:}>;
```

## Scalar objects

```php
$string = (' hello, world ')->trim()->explode(',');
```

## Structs

/blog/readonly-or-private-set

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
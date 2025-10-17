---
title: 'What I would change about PHP'
next: new-in-php-82
meta:
    description: "Things I'd change about PHP"
---

If _you_ could change something about PHP, without having to worry about backwards compatibility or breaking changes; what would it be? I know for sure I'd change a thing or two — or ten. 

I like thinking about these topics. Not because I believe all of these things necessarily need be added to PHP, but because it's good to challenge our own views and critically think about language design.

I'll keep this list short and to the point, but I'll always link to more in-depth content where relevant. Let's take a look!

{{ ad:carbon }}

## Generics 🥺

The obvious one first. If I'd have to order my wishlist in descending priority, generics are on places one to five, and the rest follows afterwards.

```php
function app(<hljs type>classString</hljs><<hljs generic>ClassType</hljs>> $className): <hljs generic>ClassType</hljs>
{
    // …
}
```

It's pretty clear however that [generics aren't coming](https://www.youtube.com/watch?v=BN0L2MBkhNg) to PHP. That is: as long as we want to validate them at runtime. That's actually what my second wishlist-item is about.

## No more runtime type checks

It's not just because of generics, but because it would allow so much more cool stuff, without having a runtime impact: switching to an opt-in, statically analysed compiler. 

I actually wrote an in-depth article about [not needing runtime type checks](/blog/we-dont-need-runtime-type-checks). Static analysis is incredibly powerful, and it would benefit PHP's growth a lot if we could get rid of "trying to do everything at runtime". 

Keep in mind: it's my opinion, you don't have to agree 😉.

## A superset of PHP

Like TypeScript for JavaScript. Imagine what would be possible if we'd be able to compile a superset language to plain PHP: complex static type checking, lots of cool syntax that would be too difficult to implement with PHP's runtime constraints, generics 🥺, … 

There are _a lot_ of caveats and sidenotes to be made here. I made a little [vlog](https://www.youtube.com/watch?v=kVww3uk7HMg) about the topic a while ago where I discuss some of the cons, but still I hope that one day this dream may become reality.

## Cascading attributes

This one might actually be doable: an easy way of getting attributes from parent classes. 

So instead of writing this:

```php
$attributes = [];

do {
    $attributes = $reflection-><hljs prop>getAttributes</hljs>(
        <hljs prop>name</hljs>: <hljs type>RouteAttribute</hljs>::class,
        <hljs prop>flags</hljs>: <hljs type>ReflectionAttribute</hljs>::<hljs prop>IS_INSTANCEOF</hljs>
    );
    
    $reflection = $reflection-><hljs prop>getParentClass</hljs>();
} while ($attributes === [] && $reflection);
```

We could do this:

```php
$attributes = $reflection-><hljs prop>getAttributes</hljs>(
    <hljs prop>name</hljs>: <hljs type>RouteAttribute</hljs>::class,
    <hljs prop>flags</hljs>: <hljs type>ReflectionAttribute</hljs>::<hljs prop>IS_INSTANCEOF</hljs> 
         | <hljs type>ReflectionAttribute</hljs>::<hljs prop>CASCADE</hljs>
);
```

## Scalar objects

It's not that high up on my list since there are userland implementations available, although it would be nice if scalar objects could have proper names. So `<hljs type>String</hljs>` instead of `<hljs type>StringHelper</hljs>` (which is how Laravel solves the problem of `<hljs type>String</hljs>` being a reserved word).

```php
$string = new <hljs type>String</hljs>(' hello, world ')-><hljs prop>trim</hljs>()-><hljs prop>explode</hljs>(',');
```

{{ cta:dynamic }}

## Pipe operator

There has been [an RFC](https://wiki.php.net/rfc/pipe-operator-v2) for it a while ago: the pipe operator. However, it has been inactive for a while now. I actually think Larry Garfield still wants to do it some day, but I'm not sure how concrete the plans are currently.

Anyway, a pipe operator would be cool:

```php
$result = "Hello World"
    |> <hljs prop>htmlentities</hljs>(...)
    |> <hljs prop>str_split</hljs>(...)
    |> <hljs prop>array_map</hljs>(<hljs prop>strtoupper</hljs>(...), $$)
    |> <hljs prop>array_filter</hljs>($$, <hljs keyword>fn</hljs>($v) => $v != 'O');
```

Note that I'm taking some creative liberties in how argument placeholders would work with the `$$` syntax. 

## Unified function names

"It would be such a breaking change 😱!!!" to finally make all PHP functions follow the same naming convention. No more `<hljs prop>str_replace</hljs>` and `<hljs prop>strlen</hljs>`, but rather `<hljs prop>string_replace</hljs>` and `<hljs prop>string_length</hljs>`.

It wouldn't be that big of a breaking change though: we could automate the upgrade process with tools like [Rector](https://github.com/rectorphp/rector), and static analysers would tell us about the correct function names while writing code. It would take some getting used to, but at least there would be a consistent API.

## Stricter everything

If you weren't yelling at me by now, many of you probably will after reading this section. I'd make PHP much stricter overall. Why? Because [I like clarity](/blog/uncertainty-doubt-and-static-analysis) in my code. A stricter language means less room for interpretation or behind-the-scenes-magic that leads to lots of confused moments. So that would include: 

- Final by default
- A return type is required
- Omitting a return type means void
- Everything must be typed
- Visibility modifiers are required

This will probably never happen, and that's ok; I can enforce most of these things by using PHP CS anyway.

## Looking back

On a final note, I made a [similar list](/blog/php-reimagined) to this one in the past, and I'm actually happy to see that some of the things I wished for back then are implemented in PHP today:

- [Enums](/blog/php-enums)
- [Readonly properties](/blog/php-81-readonly-properties)
- [Named arguments](/blog/php-8-named-arguments)
- [Improved type variance](/blog/new-in-php-74#improved-type-variance-rfc)

So, who knows! Many more things of this list might end up in PHP after all? Please let it be generics though 🥺!

What would you change about PHP? Let me know on [Twitter](*https://twitter.com/brendt_gd) or send me [e-mail](mailto:brendt@stitcher.io)!

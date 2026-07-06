---
title: Static Analysis
description: Learn how modern PHP and static analysis go hand in hand.
image: meta/php/06-static-analysis.png
---

We've already touched on PHP's type system in [chapter 2](/php/the-basics/basic-syntax#types) where we explained how PHP has runtime-checked type annotations. As you can imagine, runtime type checks are expensive, especially since a type checker can generally guarantee correctness without running a single line of code. That's why around 10 years ago, community members built the first static type checker for PHP. These days, there are a number of full-fledged static analyzers out there: tools that analyse your codebase without running the code, detecting problems along the way.

So, what's in it for you, you might wonder? Why would you want to use an additional tool that checks your code before running it? Here are a couple of reasons:

- You'll deploy fewer bugs to production
- You'll write clearer and more robust code
- You'll be faster, since all checks are automated

## Static analysis explained

Let's take a look at a very basic example to explain how static analysis works. Let's pretend we're a static analyzer for a moment. Now look at this function:

```php
function foo(array $input): void {}
```

Imagine we'd call this function, but accidentally pass it a string instead of an array:

```php
foo('wrong input');
```

Of course, this function would throw a TypeError when running it. Though just by looking at it, we could already tell it was wrong: a function that accepts an array, will not accept a string. This is the core principle of any static analyzer: looking at type definitions and function calls; and determining whether those operations are valid or not.

It might seem like a simple thing to do, but when we automate this process and scan all of our source code; static analyzers can detect quite a lot of edge cases that we might have missed otherwise.

You do pay a small price to enjoy all the benefits that come with static analysis: you'll have to properly use PHP's type system. In fact, most static analyzers won't only take PHP's built-in types into account but will also look at documentation comments to add more complex features. The more type information available, the better.

Doesn't that become tedious? Wouldn't it be faster to run your code and see whether it works? On a small scale, the answer might be yes — maybe? But on a large scale, relying on tools that scan all of your code for you in seconds definitely wins.

So, for the sake of completeness, let's mark our previous example as an error — the same way any static analyzer would do:

```php
foo({:hl-error:'wrong input':});
```

## Different kinds of analyzers

In the world of PHP and static analysis, there are a couple of options out there. It might be difficult to know which one to choose. The most popular option currently is [PHPStan](https://phpstan.org/), a static analyzer written in PHP itself. Other options are [Psalm](https://psalm.dev/) or [Phan](https://github.com/phan/phan), and the most recent addition is [Mago](https://mago.carthage.software/). What sets Mago apart is that it's written in Rust, making it a lot faster. Mago also includes a more varied set of static checks and code formatters.

It's not unimportant to mention that all these tools are built around the idea that they analyze your codebase as a whole, in bulk. Real-time analysis as you're coding is equally important, and for that my (and many others) preference is [PhpStorm](https://www.jetbrains.com/phpstorm/). PhpStorm gives you realtime feedback as you're writing code, based on the same principle of static analysis. However, realtime static analysis is limited by one crucial factor: the performance cost. When you're analyzing your code base in bulk like with Mago, it's ok to wait for a second or two; within the context of an IDE, those kinds of waiting times are unacceptable when you're writing code.

That's why a combination of a proper IDE and one or more static analyzers is the best option. PhpStorm catches the basic mistakes, while tools like Mago and PHPStan can take their time to really dig into your code.

## What static analysis can do

We already looked at one example, where a static analyzer tells you that you've provided the wrong input:

```raw
// php
{:hl-keyword:function:} {:hl-property:foo:}({:hl-type:array:} $input): {:hl-type:void:} {}

{:hl-property:foo:}({:hl-error:'wrong input':});
```

Not only that, these tools will also tell you when variables are unused, like the `$input` parameter, for example:

```raw
{:hl-keyword:function:} {:hl-property:foo:}({:hl-type:array:} {:hl-error:$input:}): {:hl-type:void:} {}
```

Static analyzers will also report conditions that can never be reached:

```php
$condition = /* some kind of boolean expression */;

if ($condition) {
    // …
} elseif ({:hl-error:$condition:}) {
    // …
}
```

These are isolated examples, but keep in mind that static analyzers evaluate your whole codebase at once. They are able to detect these kinds of issues across files, in deeply nested structures, etc. And that's where static analysis truly shines: where the human mind isn't able to keep an overview anymore because there simply is too much code.

We already mentioned that static analyzers heavily rely on PHP's type system to gain as many insights in our code as possible. That's why they will also notify you when type information is missing:

```php
class Post
{
    public function {:hl-error:title():} { /* … */ }
}
```

On top of that, these tools extend PHP's runtime type system to allow for much more complex type validation. They allow for pretty cool functionality, for example to determine whether a string is an actual class name, whether it's a callable, and they even support generics.

## Extended doc blocks

Let's take a closer look at these extended type systems. Static analysis tools don't need to run PHP code to understand it, which means they aren't limited to PHP's built-in syntax either. They could read anything in comments and give it special meaning as well. That's exactly what happened: all static analysis tools support custom type annotations that live in special comments denoted by `/**` and `*/`

Like, for example, defining an _array of a specific type_ variable:

```php
/** @var Book[] $books */
$books = [new Book('Timeline Taxi')];
```

Or full blown generic types:

```php
/** @template TCollectionItem */
final class Collection 
{
    /* … */
}

/** @var Collection<Book> $books */
$books = new Collection(new Book('Timeline Taxi'));
```

By the way, if you read my series on [generics in PHP](https://stitcher.io/blog/generics-in-php-1) if you want to learn more about them. These docblocks also support a special type called `{:hl-type:class-string:}` that represent only valid class names:

```php
/** @param class-string $className */
function make(string $className): mixed
{
    /* … */
}
```

And class-strings can be combined with generic types as well:

```php
/**
 * @template TClassName of object
 * @param class-string<TClassName> $className
 * @return TClassName
 */
function get(string $className): mixed
{
    /* … */
}
```

There are callable types:

```php
/** @return \Closure(Foo $foo): int */
function returnsClosure(): Closure
{
    /* … */
}
```

And more. However, what's important to note is that these docblock type annotations differ between tools. That's why you'll need to read the documentation of whatever tool you choose to know what features are available.

## In practice

My advice would be to use a static analyzer from day 1 whenever you start a new project. You can find my personal setup for new projects in [this repository](https://github.com/tempestphp/package). These days I prefer Mago, since it's much more performant, but also because it bundles more QA tools than just a static analyzer. We'll talk about those in the next chapter as well.
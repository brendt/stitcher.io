In the world of PHP and static analysis, there are a couple of options out there. It might be difficult to know which one to choose. There's [PHPStan](https://phpstan.org/), [Psalm](https://psalm.dev/), [Phan](https://github.com/phan/phan), and of course [PhpStorm](https://www.jetbrains.com/phpstorm/). PhpStorm? Isn't that an IDE? Well yes, but one of its core parts _is_ a static analyser. That's why PhpStorm is able to tell you that writing this code is wrong, while you're writing it:

```php
function foo(<hljs type>array</hljs> $input): void {}

<hljs prop>foo</hljs>(<hljs striped>'wrong input'</hljs>);
```

Here's already an important distinction to make: realtime static analysis like PhpStorm does is extremely useful when writing code, but it doesn't catch all the issues other static analysers do. The reason is simple: static analysis comes with a performance cost, and you need to be very careful about the impact of it when writing code in realtime.

That's why a combination of a proper IDE and one or more static analysers are the best option. PhpStorm catches the basic mistakes, while tools like Psalm and PHPStan can take their time to really dig into your code.

By the way, because standalone static analysers require a little more horsepower, it's a good idea to run them using CI and not just locally — but I'm getting ahead of myself, we'll discuss CI setups in a dedicated mail.

Back to our three standalone tools: Psalm, PHPStan and Phan — which one is the best? There's definitely not one "best" solution out there. PHPStan and Phan are a little older, but Psalm was created for and by Vimeo, so it has quite a lot of in-depth options. Phan on the other hand requires you to install the [php-ast](https://github.com/nikic/php-ast) extension. A little more setup work, but resulting in much faster analysis.

In this series, we'll mainly look at Psalm and PHPStan though, and we'll dedicate one mail to Phan. For me, there's two main differences between Psalm and PHPStan, but they are highly subjective.

I personally like Psalm's config and tooling better than PHPStan. Psalm uses a proper XML scheme, which allows an IDE like PhpStorm to actually autocomplete config options. PHPStan uses neon files which — to be honest — I never heard of before using PHPStan. This is definitely not a deal-breaker, but it is something I noticed when switching between Psalm and PHPStan.

Here's psalm's most basic configuration file:

```txt
<?<hljs keyword>xml</hljs> <hljs prop>version</hljs>="1.0"?>
<<hljs keyword>psalm</hljs>>
    <<hljs keyword>projectFiles</hljs>>
        <<hljs keyword>directory</hljs> <hljs prop>name</hljs>="src" />
    </<hljs keyword>projectFiles</hljs>>
</<hljs keyword>psalm</hljs>>
```

##### psalm.xml

And here's PHPStan's:

```txt
<hljs prop>parameters</hljs>:
    <hljs prop>level</hljs>: 6
    <hljs prop>paths</hljs>:
        - src
        - tests
```

##### phpstan.neon

We'll definitely discuss these config files in more depth in a later mail. What we won't cover is how to install them in your project. Both analysers have great documentation, and it would be counter-productive trying to replicate it here. We'll focus on the fun stuff instead! 

The other big difference is that PHPStan tends to have better integration for Laravel; this is of course only relevant if you're using Laravel (like I do). Given the amount of magic that Laravel uses, it's not always trivial for static analysers to properly understand your code, sometimes resulting in false positives, sometimes in false negatives. Both PHPStan and Psalm _have_ a dedicated Laravel plugin ([Larastan](https://github.com/nunomaduro/larastan) and [psalm-plugin-laravel](https://github.com/psalm/psalm-plugin-laravel)), though I find that Larastan is just a little more stable over time.

Anyway, I'd advice to try out both of them. You _could_ actually run both of them in the same project, both Psalm and PHPStan do sometimes discover different issues. I find that there's an overhead with running both static analysers in the same project though, and do prefer to only use one.

---

So that's where we are: feel free to try out one or both analysers, and make sure to combine them with a proper IDE. Tomorrow we'll look at Psalm and PHPStan _in action_. 

See you then!

Brent

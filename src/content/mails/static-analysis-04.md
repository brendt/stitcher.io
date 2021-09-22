Ok so, we can do quite a lot with static analysis, it all seems nice _in theory_. But here I have a project that's 4 years old and when I add Psalm or PHPStan, I get over 3000 issues reported. Now what?

Trying to fix all such issues at once is a daunting task — a counterproductive one if you ask me: the project has been in production for years and, ok, there might be issues, but it still works. It's actually quite scary fixing code that's broken according to your static analyser when you're not sure about that side effects that might have.

Don't panic. There are several strategies to go about improving your project's static analysis results, without pulling out the big guns.

## Error Levels

First of all, both Psalm and PHPStan allow you to configure a so-called "error level". For example, when installing Psalm, you should initialize it when first running it:

```
./vendor/bin/psalm --init
```

This command will initialize the `psalm.xml` config file, but will also scan your codebase, and determine what the best error level is to get started with. In Psalm, the lower the lever, the more strict it is. It goes from [level 1 to level 8](https://psalm.dev/docs/running_psalm/error_levels/), with 8 being the most lenient.

Inspecting `psalm.xml` after initialization, you can see how the error level is stored in the XML. Feel free to change it if you want to try out other levels:

```txt
<<hljs keyword>psalm</hljs>
  <hljs prop>errorLevel</hljs>="4"
>
    <hljs comment><!-- … --></hljs>
</<hljs keyword>psalm</hljs>>
```

##### psalm.xml

PHPStan [turns things around](https://phpstan.org/user-guide/rule-levels): level 0 is the most lenient, while level 9 the most strict. You can use the `-l` flag to override the level when analyzing your codebase:

```
./vendor/bin/phpstan analyse -l 6 src tests
```

And of course, it's also saved in the config file:

```txt
<hljs prop>parameters</hljs>:
    <hljs prop>level</hljs>: 6
```

##### phpstan.neon

## Ignore Errors

Sometimes, you simply want to ignore an error. You want to tell the static analyzer that, yes, you know this is an errors; but it's ok for now. There are several ways to do this. 

First of all, you can use docblocks to tell Psalm or PHPStan to specifically ignore a line:

```php
function foo() {
    /** @<hljs prop>phpstan-ignore-next-line</hljs> */
    <hljs prop>var_dump</hljs>($undefined);
}

function bar() {
    /** @<hljs prop>psalm-suppress</hljs> <hljs type>UndefinedVariable</hljs> */
    <hljs prop>var_dump</hljs>($undefined);
}
```

##### Both the Psalm and PHPStan variants

On the other hand, you can also globally ignore issues, for example in Psalm's config file:

```txt
<<hljs keyword>psalm</hljs>>
    <<hljs keyword>issueHandlers</hljs>>
        <<hljs keyword>MissingPropertyType</hljs> <hljs prop>errorLevel</hljs>="suppress" />
    </<hljs keyword>issueHandlers</hljs>>
</<hljs keyword>psalm</hljs>>
```

##### psalm.xml

Or in PHPStan's:

```txt
<hljs prop>parameters</hljs>:
    <hljs prop>ignoreErrors</hljs>:
        - '#Call to an undefined [a-zA-Z0-9\\_]#'
```

You can see how Psalm uses XML entries to specifically disable issues, while PHPStan uses regex scanning on error messages. 

There's of course more to say about configuring these error levels, you can read all about them in the [Psalm](https://psalm.dev/docs/running_psalm/dealing_with_code_issues/) and [PHPStan](https://phpstan.org/user-guide/ignoring-errors) docs.

> Can I quickly pitch in before moving on to the last section of this mail? I just wanted to remind you how much I appreciate it you're reading these mails. If you made it here, I think it's safe to assume you like the series? If there's anything that can be improved, if you have any questions or interesting thoughts, don't hesitate to hit reply!
> 
> Also, if you want to, can I ask you to [share](https://road-to-php.com/static) the series website with your friends and followers? Word of mouth really is the best way to get more people excited about static analysis, and you're playing an important role. Let's get everyone excited about modern PHP! 

## The Baseline

The last option available — this _is_ the big gun — is the "baseline". Whenever you run Psalm or PHPStan, you can tell it to generate a baseline file. A baseline is a large file containing all current errors, and telling Psalm or PHPStan to simply ignore them.

This allows you to start from a clean slate, even in large projects, so that you can use static analysis in _new_ parts of the codebase, while not having to touch the older parts. Maybe you can refactor those old parts incrementally over time, or maybe you leave some of them alone — that's up to you and the project requirements.

There is some configuration involved with setting up a baseline, so make sure to read [Psalm's](https://psalm.dev/docs/running_psalm/dealing_with_code_issues/#using-a-baseline-file) or [PHPStan's](https://phpstan.org/user-guide/baseline) documentation about it.

---

We're getting somewhere! Not only is static analysis pretty useful in theory, we now also know how to use it in real-life projects. I'm looking forward to the next days, where we'll take a deep dive in more complex static analysis features.

Until tomorrow!

Brent

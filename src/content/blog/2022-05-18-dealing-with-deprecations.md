You've probably dealt with them at one point your programming career:

```txt
<hljs full red>Deprecated: Creation of dynamic property Post::$name 
is deprecated in /in/4IreV on line 10</hljs>
```

Deprecation notices 🤢

Despite them being annoying and frustrating to many developers, they actually serve a purpose. I would even say you will appreciate them once you understand their goal, and how to deal with them. Let's take a look!

## 1. Deprecations are helpful

It's a common complaint: "why do my PHP scripts break with minor version updates??". And quite right: PHP has a tendency to add deprecation notices in minor releases, which tend to be audibly present when upgrading a project. Take for example [PHP 8.1](/blog/new-in-php-81#interal-method-return-types-rfc), where suddenly logs were filled with these kinds of warnings:

```txt
<hljs red full>Return type should either be compatible with 
IteratorAggregate::getIterator(): Traversable, 
or the #[ReturnTypeWillChange] attribute should be used 
to temporarily suppress the notice</hljs>
```

It's important to understand what deprecations are about: they aren't errors, they are _notices_. They are a way of notifying PHP developers about a breaking change in the future. They want to warn you up front, to give you plenty of time to deal with that upcoming breaking change.  

Of course, one could ask: are these breaking changes and fancy features really necessary? Do we _really_ need to change internal return types like `<hljs type>IteratorAggregate</hljs>::<hljs prop>getIterator</hljs>(): <hljs type>Traversable</hljs>`, do we _really_ need to [disallow dynamic properties](/blog/new-in-php-82#deprecate-dynamic-properties-rfc)?

In my opinion — and it's shared by the majority of PHP internal developers — yes. We need to keep improving PHP, it needs to grow up further. And that sometimes means introducing a breaking change, like for example when internals add return types to built-in class methods: if you're extending `<hljs type>IteratorAggregate</hljs>` in userland code, you will need to make some changes. The language needs to evolve. 

Overall I'd say that, despite some of the annoyances that come with such an evolving language, it's for the better.

And luckily we have a mechanic like deprecation notices: they tell us that something will break in the future, but that we can still use it today. We can incrementally make changes and updates around our codebase.

## 2. Deprecations can be silenced

Second, PHP internals go to great lengths to help userland developers in dealing with deprecations. Thanks to the addition of [attributes in PHP 8.0](/blog/attributes-in-php-8), we now have a much better and standardized way of communication between our code and PHP's interpreter.

For example: you can tag userland code with the `<hljs type>ReturnTypeWillChange</hljs>` attribute in order to prevent deprecation warnings being thrown.

```php
final class MyIterator implements \IteratorAggregate
{
    #[\<hljs type>ReturnTypeWillChange</hljs>]
    public function getIterator()
    {
        // …
    }
}
```

Of course, this code will break in PHP 9.0, so while silencing deprecation notices is a short-term solution; you will need to fix them if you ever want to upgrade to the next major version:

```php
final class MyIterator implements \IteratorAggregate
{
    public function getIterator(): \Traversable
    {

    }
}
```

One more example maybe? With [dynamic properties being deprecated in PHP 8.2](/blog/new-in-php-82#deprecate-dynamic-properties-rfc), you can mark classes with the `<hljs type>AllowDynamicProperties</hljs>` attribute, making it so that they allow dynamic properties again and suppress the deprecation notice:

```php
#[<hljs type>AllowDynamicProperties</hljs>]
class Post
{
}

// …

$post-><hljs prop>title</hljs> = 'Name';
```

## 3. They are notices, not fatal errors

PHP code will keep working just fine, even when parts of it trigger deprecation notices. Of course, you know by now that it's in your best interest to fix them if you ever want to upgrade to the next major version, but you don't need to do it right now. It's perfectly ok to upgrade your production projects and deal with deprecations notices over time.

I'd even recommend disabling deprecation notices on production altogether, or at least not show them to your end users:

```php
<hljs prop>error_reporting</hljs>(<hljs prop>E_ALL</hljs> ^ <hljs prop>E_DEPRECATED</hljs>);
```

Maybe you can keep track of them using an external error tracker for the first months, to get a clear image of the places you'll need to fix those deprecations. But above all: deprecation notices shouldn't be blockers when upgrading to the latest minor PHP version. 

## 4. Automation

Lastly, keep in mind that you don't need to do the boring tasks by hand. There are tools like [Rector](https://github.com/rectorphp/) and [phpcs](https://github.com/squizlabs/PHP_CodeSniffer/) that can take care of many upgrading issues for you. Usually it's a matter of running a script that takes a couple of minutes at most to scan and fix your codebase. That's work that you might need days for if you'd do it by hand.

It's not difficult or time consuming anymore to deal with PHP upgrades. In fact, deprecations help tremendously to create a smoother upgrade path and prepare your codebase for the future. 

I like deprecations, you should too.

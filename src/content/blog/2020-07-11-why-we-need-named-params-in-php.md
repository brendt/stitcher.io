There's a new RFC in town for [PHP 8](/blog/new-in-php-8), and its name is the [named arguments RFC](*https://wiki.php.net/rfc/named_params).

If you're eligible to vote, or know someone who can: I want to ask you to take five minutes to read this, and to be clear up front: I want you to vote yes.

Here's why from the point of view of a userland developer, both for client projects _and_ [open source](*https://spatie.be/open-source).

{{ ad:carbon }}

## As an OSS maintainer

The main argument against named arguments — the PHP 8 puns continue — is that they would make maintaining open source software a pain: changing the name of an argument would become a breaking change.

Here's what that means. Imagine an open source package which has this function as part of its public API:

```php
public function toMediaCollection(<hljs type>string</hljs> $collection, <hljs type>string</hljs> $disk = null);
```

Named parameters would allow to call this function like so:

```php
$pendingMedia
    -><hljs prop>toMediaCollection</hljs>(<hljs type>collection</hljs>: 'downloads', <hljs type>disk</hljs>: 's3');
```

If, for some reason, the open source maintainer would want to change the name of the `$collection` or `$disk` variables, they would have to tag a major release, because named arguments would make that a breaking change.

Now, let me tell you something from my point of view as an open source maintainer: this rarely happens. 

As a matter of fact, I can only think of a handful occurrences. And the only reason we decided to do renames on those occurrences, was because we were already working on a new major version and we figured we might as well improve the naming a little bit while we were at at.

I'm not the only one with that opinion by the way, Nicolas Grekas is amongst the people voting yes, and he knows a thing or two about OSS development. Oh and here's [Mohamed Said](*https://twitter.com/themsaid/status/1281819955231690753?s=20), one of the core maintainers of Laravel:

> I've been working at Laravel for 4 years now and I rarely find us wanting to change argument names in a refactor. We either add or remove arguments, but I'd say we never had to change names.

Actually that's an interesting thought: argument lists already _are_ prone to breaking backwards compatibility: changing the order of arguments already is a breaking change! And we're dealing with that just fine now, aren't we?

Now even _if_ you, as an open source maintainer, don't want to take the responsibility of making sure argument names don't change between major releases, here's what you do: tell your users you won't actively support named arguments, and using them is at their own risk. Just put this in the README:

```
**Heads up**: this package doesn't actively support named arguments. 
This means that argument names might change in minor and patch releases. 
You can use them, but at your own risk.
```

Don't deny all PHP developers this flexibility, because you're afraid of a slight chance it might break something somewhere in the far far future. Don't be afraid.

## As a programmer doing client work

Another argument is that this RFC would encourage bad API design. It would encourage people to write large method definitions, which in turn often indicates a code smell.

I as well can come up with lots of examples that aren't a good fit for named parameters. But that doesn't mean there are no use cases for them at all! Have you heard of [data transfer objects](/blog/laravel-beyond-crud-02-working-with-data) or value objects before? If you're following this blog, chances are you have.  

I'm not going to copy my writing on them in this post, but I can summarise the main thought behind them: treat data as a first class citizen of your application, model them with objects. For example: an address has a street, number, postal code, city, country, sometimes even more than that. That data should be represented by a strongly typed object in PHP, and not passed between contexts as an array full of random stuff, its constructor would look like this:

```php
class Address
{
    public function __construct(
        <hljs type>string</hljs> $street,
        <hljs type>string</hljs> $number,
        <hljs type>string</hljs> $postal,
        <hljs type>string</hljs> $city,
        <hljs type>string</hljs> $country,
    ) { /* … */ }
}
```

DTOs and VOs are valid cases where these kinds of large constructors are allowed, it's no code smell at all. I had a quick look at [an old project](/blog/a-project-at-spatie) of ours, at the time of tracking its stats, it already had 63 DTO classes, and the project was far from finished at that point!

Large constructors happen, and named parameters would not only add more clarity, but also offer the flexibility of changing the parameter order after the fact, without having to worry about fixing the order at all.

Take the our `Address` object, for example. Let's say we need to support number suffixes. We can add that argument without having to worry about the order that other places called it:

```php
class Address
{
    public function __construct(
        <hljs type>string</hljs> $street,
        <hljs type>string</hljs> $number,
        <hljs green><hljs type>string</hljs> $numberSuffix,</hljs>
        <hljs type>string</hljs> $postal,
        <hljs type>string</hljs> $city,
        <hljs type>string</hljs> $country,
    ) { /* … */ }
}
```

Sure the calling site still need to add it, but at least you don't have to worry about micro managing the parameter order anymore. 

But what if users decide to use ordered arguments instead? You'd need some way to ensure named arguments are used in these cases. The answer is surprisingly dull: establish conventions with your team, and optionally enforce them with tools like phpcs.

Yes, ideally, we'd want the language to prevent us from any possible misstep; but that simply isn't a realistic expectation. To me, that still isn't an argument for voting against this RFC. I've been working with teams of developers for years now, and project conventions need to be established anyway. They work just fine.

## Dealing with PHP's own legacy

Pop quiz! How to set a cookie without a value, which expires two hours from now?

Did you look up the docs or consult your IDE?

That's fine, it's a confusing function after all. Named arguments can offer a little more clatiry though. Compare the two following notations:

```php
<hljs prop>setcookie</hljs>(
    'test', 
    '', 
    <hljs prop>time</hljs>() + 60 * 60 * 2
);
```

Or:

```php
<hljs prop>setcookie</hljs>(
    <hljs type>name</hljs>: 'test',
    <hljs type>expires</hljs>: <hljs prop>time</hljs>() + 60 * 60 * 2,
);
```

I know which I would pick, everyone benefits from named arguments in this case. And since chances are slim that PHP's internal functions will change anytime soon, it's another very good reason to add them.

## Looking at other languages

Lastly, let's face the simple facts: several other languages — many also focused on web development — already support named arguments. Some deal with them in slightly different ways, but the base concept is known to many other programmers, and it's a good thing.  

Here are a few examples:

- [Python](*https://treyhunner.com/2018/04/keyword-arguments-in-python/)
- [Ruby](*https://thoughtbot.com/blog/ruby-2-keyword-arguments)
- [C#](*https://docs.microsoft.com/en-us/dotnet/csharp/programming-guide/classes-and-structs/named-and-optional-arguments)
- [Swift](*https://useyourloaf.com/blog/swift-named-parameters/)
- [Kotlin](*https://kotlinlang.org/docs/reference/functions.html#named-arguments)

So let's not spread fear about harder-to-maintain code, or open source software that will become a nightmare to maintain. Named arguments are a known feature in the larger software community, and have proven their worth. No need for hypothetical problems, we will manage. 

---

By the way, right now the vote is 40 yes and 14 no, meaning the RFC would pass at this point. If you can vote and haven't done yet: do it now, and vote yes! If you voted no and read this post, I'd like you to reconsider you vote, thanks!

There's a new RFC in town, and its name is the [named arguments RFC](*https://wiki.php.net/rfc/named_params).

If you're eligible to vote, or know someone who can: I want to ask you to take five minutes to read this, and to be clear up front: I want you to vote yes.

Here's why, from the point of view of a userland developer, both in client projects _and_ [open source](*https://spatie.be/open-source).

## As an OSS maintainer

The main argument against named arguments — the PHP 8 puns continue — is that they would make maintaining open source software a pain, because changing the name of an argument is now a breaking change.

Here's what that means. Imagine an open source package has this function as part of their API:

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

As a matter of fact, I can only think of a handful occurrences. And the only reason we decided to do those renames on those occurrences, was because we were already working on a new major version and we figured we might as well improve the naming a little bit while we were at at.

I'm not the only one with that opinion by the way, here's [Mohamed Said](*https://twitter.com/themsaid/status/1281819955231690753?s=20), one of the core maintainers of Laravel:

> I've been working at Laravel for 4 years now and I rarely find us wanting to change argument names in a refactor. We either add or remove arguments, but I'd say we never had to change names.

Actually that's an interesting thought: argument lists already _are_ prone to breaking backwards compatibility: changing the order of arguments already is a breaking change! And we're dealing with that just fine now, aren't we?

Now even _if_ you, as an open source maintainer, don't want to take the responsibility of making sures argument names don't change between major releases, here's what you do: tell your users you won't actively support named arguments, and using them is on their own risk. Just put this in the README:

```
**Heads up**: this package doesn't actively support named arguments. 
This means that argument names might change in minor and patch releases. 
You can use them, but at your own risk.
```

Don't deny all PHP developers this flexibility, because you're afraid of a slight chance it might break something somewhere in the far far future. Don't be afraid.

## As a programmer doing client work

I've been doing client work with PHP since 2014. It's still my main occupancy. If there's one thing I learned over those years, it's that data is one of the most important aspects of your code. It's even so important that I [wrote a whole blogpost](/blog/laravel-beyond-crud-02-working-with-data) about it.

If you're not familiar with my writings or with DTOs and VOs, I recommend to read [that post](/blog/laravel-beyond-crud-02-working-with-data) first, because I won't repeat everything I wrote down there. 

In summary: I highly encourage you to treat data as a first-class citizen in our application. It's even so important that I wrote a [dedicated package](*https://github.com/spatie/data-transfer-object) for it. My wish is that one day, I can throw it away and do everything in PHP itself. 

This package does two things: it improves PHP's type system by supporting generics, union types, and so on; as well as named parameters in constructors. That is, "a kind of named parameters": arrays…

```php
$dto = new <hljs type>MyDto</hljs>([
    'paramA' => $varA,
    'paramB' => $varB,
    …
]);
```  

There's so much value in using DTOs to represent data in your application in a structured way; so much that we put up with passing arrays to constructors, arrays that don't have autocompletion or any other static analysis. Named arguments would solve all of that.

## As someone who has to deal with PHP's legacy every time they write code

## As someone who's looking at other languages

- [Python](*https://treyhunner.com/2018/04/keyword-arguments-in-python/)
- [Ruby](*https://thoughtbot.com/blog/ruby-2-keyword-arguments)
- [C#](*https://docs.microsoft.com/en-us/dotnet/csharp/programming-guide/classes-and-structs/named-and-optional-arguments)
- [Swift](*https://useyourloaf.com/blog/swift-named-parameters/)
- [Kotlin](*https://kotlinlang.org/docs/reference/functions.html#named-arguments)

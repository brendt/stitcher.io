We've already seen how docblocks can add rich functionality to static analysers: they aren't limited by PHP's built-in type system so it's trivial (from a syntax point of view) to add features like generics.

There's more than  generics though. Today I want to share some other annotations that might be useful to you.

## Class Strings

You probably pass class names throughout your codebase somewhere, one example I can think of is in the container:

```php
$container-><hljs prop>make</hljs>(<hljs type>PostRepository</hljs>::class);
```

Both Psalm and PHPStan support a `class–string` annotation that ensures that only valid class names are passed to such functions:

```php
/**
 * @param <hljs type>class-string</hljs> $className
 * @return <hljs type>mixed</hljs>
 */
function make(<hljs type>string</hljs> $className): mixed
{
    // …
}
```

Psalm takes these annotations a step further, also supporting `<hljs type>trait-string</hljs>` and `<hljs type>interface-string</hljs>`.

## String Annotations

Besides class-related strings, both Psalm and PHPStan also support types like `<hljs type>lowercase-string</hljs>`, `<hljs type>non-empty-string</hljs>`, `<hljs type>literal-string</hljs>`, `<hljs type>numeric-string</hljs>` and more. 

There are subtle differences between [Psalm](https://psalm.dev/docs/annotating_code/type_syntax/scalar_types/) and [PHPStan](https://phpstan.org/writing-php-code/phpdoc-types), so make sure to review their documentation pages.

## Type Aliases

A cool feature of PHPStan is that you can define type aliases in its config file:

```txt
<hljs prop>parameters</hljs>:
    <hljs prop>typeAliases</hljs>:
        <hljs prop>TagOrCategory</hljs>: 'Tag|Category'
```

##### phpstan.neon

As well as [inline in docblocks](https://phpstan.org/writing-php-code/phpdoc-types#local-type-aliases):

```php
/**
 * @<hljs text>phpstan-type</hljs> 
 *      <hljs type>UserAddress</hljs> <hljs type>array</hljs>{
 *          <hljs prop>street</hljs>: <hljs type>string</hljs>, 
 *          <hljs prop>city</hljs>: <hljs type>string</hljs>, 
 *          <hljs prop>zip</hljs>: <hljs type>string</hljs>
 *      }
 */
class User {}
```

Aliasing a union type might not be as useful, but aliasing complex array structures could be very beneficial in many places:

```php
/**
 * @param <hljs type>UserAddress</hljs> $address
 */
function setAddress(<hljs type>array</hljs> $address) 
{
    // …
}
```

## Callable Types

Another complex type definition is the callable type. Both [Psalm](https://psalm.dev/docs/annotating_code/type_syntax/callable_types/) and [PHPStan](https://phpstan.org/config-reference#vague-typehints) also support a callable type annotation:

```php
/**
 * @return <hljs type>\Closure(Foo $foo): int</hljs>
 */
function returnsClosure(): Closure {
    // …
}
```

I find this one particularly useful: it allows developers to have much more control over what a closure should do. One example where this kind of definition is especially useful, is in frameworks where users are often expected to pass in closures to framework core functions. 

---

There are of course more little features like these in [Psalm](https://psalm.dev/docs/annotating_code/supported_annotations/) and [PHPStan](https://phpstan.org/writing-php-code/phpdocs-basics). And you'll notice that these are the places where both analysers have subtle differences between them.

I don't think one is necessarily better than the other, but it's these kinds of details that might influence your decision on which analyser to use, based on your project's requirements.

Tomorrow we'll take another direction and discuss when and how to run static analysers. 

See you then!

Brent

---
title: 'Deprecating spatie/data-transfer-object'
---

It's been four years since I published the first version of [spatie/data-transfer-object](https://github.com/spatie/data-transfer-object) together with my then-colleagues at Spatie.

Back then, PHP 7.3 was just around the corner and the package started out as a way to add complex runtime type checks for class properties. It gave programmers certainty about whether they were actually dealing with the right data in a typed way:

```php
class PostData extends DataTransferObject
{
    /**
     * Built in types: 
     *
     * @var <hljs type>string</hljs> 
     */
    public <hljs prop>$property</hljs>;
    
    /**
     * Classes with their FQCN: 
     *
     * @var <hljs type>\App\Models\Author</hljs>
     */
    public <hljs prop>$property</hljs>;
    
    /**
     * Lists of types: 
     *
     * @var <hljs type>\App\Models\Author[]</hljs>
     */
    public <hljs prop>$property</hljs>;
    
    /**
     * Iterator of types: 
     *
     * @var <hljs type>iterator<\App\Models\Author></hljs>
     */
    public <hljs prop>$property</hljs>;
    
    /**
     * Union types: 
     *
     * @var <hljs type>string|int</hljs>
     */
    public <hljs prop>$property</hljs>;
    
    /**
     * Nullable types: 
     *
     * @var <hljs type>string|null</hljs>
     */
    public <hljs prop>$property</hljs>;
    
    /**
     * Mixed types: 
     *
     * @var <hljs type>mixed|null</hljs>
     */
    public <hljs prop>$property</hljs>;
    
    /**
     * Any iterator: 
     *
     * @var <hljs type>iterator</hljs>
     */
    public <hljs prop>$property</hljs>;
    
    /**
     * No type, which allows everything
     */
    public <hljs prop>$property</hljs>;
}
```

Fast forward a year to PHP 7.4, and typed properties were added. From the very start I said that one of the package's goal was to become obsolete in the future, and it seemed like we were one step closer to achieving it.

However, another (accidental) feature of the package started to gain popularity: the ability to cast raw data into nested DTOs. So a DTO like this:

```php
class PostData extends DataTransferObject
{
    public <hljs type>AuthorData</hljs> <hljs prop>$author</hljs>;
}
```

Could be created from this input:

```php
$postData = new <hljs type>PostData</hljs>([
    'author' => [
        'name' => 'Foo',
    ],
]);
```

So while typed properties were now a thing, we decided to continue the package, albeit in a slightly other form.

Next came along PHP 8.0 with attributes and named properties, allowing for even more functionality to be added:

```php
class MyDTO extends DataTransferObject
{
    public <hljs type>OtherDTO</hljs> <hljs prop>$otherDTO</hljs>;
    
    public <hljs type>OtherDTOCollection</hljs> <hljs prop>$collection</hljs>;
    
    #[<hljs type>CastWith</hljs>(<hljs type>ComplexObjectCaster</hljs>::class)]
    public <hljs type>ComplexObject</hljs> <hljs prop>$complexObject</hljs>;
    
    public <hljs type>ComplexObjectWithCast</hljs> <hljs prop>$complexObjectWithCast</hljs>;
    
    #[<hljs type>NumberBetween</hljs>(1, 100)]
    public <hljs type>int</hljs> <hljs prop>$a</hljs>;
    
    #[<hljs type>MapFrom</hljs>('address.city')]
    public <hljs type>string</hljs> <hljs prop>$city</hljs>;
}
```

At this point though, we were far away from the problem the package initially set out to solve. It did no more runtime type checking: in part because of PHP's improved type system, in part because I believe static analysis is a much better approach to solving type-related problems these days.

On top of that, there are better solutions to "data mapping" than what this package does: there's [spatie/laravel-data](https://github.com/spatie/laravel-data) with an incredible Laravel-specific approach to mapping data between requests, databases, views, etc; there's [cuyz/valinor](https://github.com/CuyZ/Valinor) which offers much more functionality than our package; and there is [symfony/serializer](https://symfony.com/doc/current/components/serializer.html) which is a little more bare-bones, but more powerful as well.

And so, the question that has been on my mind for two years, has been answered: it _is_ time to deprecate `spatie/data-transfer-object`.

I of course discussed the matter with my ex-colleagues at Spatie, as well as with Aidan who has helped maintaining the package for a couple of years now. We all agreed that this is a good time:

- PHP has evolved a lot, meaning that the original goal of the package has been fulfilled.
- There are great alternatives out there, both Laravel-specific and framework agnostic.
- It's better to give the package a worthy ending, than a slow death (which is already happening, kind of).

Now, keep in mind that a deprecation doesn't mean the package is gone! The code is still here for you to use, and I don't foresee any issue for the near future. 

If you were to have any serious concerns though: don't hesitate to let me know [on Twitter](https://twitter.com/brendt_gd)!

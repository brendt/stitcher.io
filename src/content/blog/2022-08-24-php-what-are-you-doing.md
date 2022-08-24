There's a new RFC in town called [asymmetric visibility](https://wiki.php.net/rfc/asymmetric-visibility), its aim is to define properties which can be _set_ from a protected or private scope, but which from the outside (the public scope) can only be _read_.

It looks something like this:

```php
class Post
{
    public function __construct(
        <hljs keyword>public private</hljs>(<hljs keyword>set</hljs>) <hljs type>string</hljs> <hljs prop>$title</hljs>,
    ) {}
}
```

There are a couple of things going on here:

- we're using a [promoted property](/blog/constructor-promotion-in-php-8) to define the title, and
- we're explicitly saying that `<hljs prop>$title</hljs>` can be set (and thus overwritten) within the `<hljs keyword>private</hljs>` scope of `<hljs type>Post</hljs>`.

In other words, this is allowed:

```php
class Post
{
    public function __construct(
        <hljs keyword>public private</hljs>(<hljs keyword>set</hljs>) <hljs type>string</hljs> <hljs prop>$title</hljs>,
    ) {}
    
    public function changeTitle(<hljs type>string</hljs> $title): void
    {
        // Do a bunch of checks
        if (<hljs prop>strlen</hljs>($title) < 30) {
            throw new <hljs type>InvalidTitle</hljs>('Title length should be at least 30');
        }
        
        // Change the title from within the private scope
        <hljs yellow>$this->title = $title</hljs>;
    }
}
```

While this isn't:

```php
$post = new <hljs type>Post</hljs>('Title');

// Setting $title from the public scope isn't allowed:
<hljs striped>$post-><hljs prop>title</hljs> = 'Another title'</hljs>;
```

I would say it's a pretty decent proposal, I can come up with a bunch of use cases where you want public readonly access to an object's properties (without the overhead of implementing getters), while still being able to change a property's value from within its class. That class could for example add internal checks to ensure the value adheres to any number of business rules — as a simplified example: ensuring the title of our post is at least 30 characters long.

So all things good, here's hoping the RFC passes.

Right?

Well, I have a problem with it. Actually, not with the RFC _itself_ — I think it's a very good proposal. No, my concern is not with this RFC on its own, but rather with how it's closely related to readonly properties.

Here we have an RFC which scope overlaps with readonly properties (albeit not entirely the same), with as a future possibility to replace readonly properties altogether. Here's a quote from the RFC:

> At this time, there are only two possible operations to scope: read and write. In concept, additional operations could be added with their own visibility controls. Possible examples include:
>
> - …
> - once - Allows a property to be set only once, and then frozen thereafter. In this case, **public private(once) would be exactly equivalent to readonly**, whereas public protected(once) would be similar but also allow the property to be set from child classes.

It's clear that this RFC and its successors have the potential to replace readonly properties entirely. [Readonly properties](/blog/php-81-readonly-properties) — a feature that only has been added one year ago in PHP 8.1, not to mention readonly classes, which are [coming to PHP 8.2](/blog/new-in-php-82) later this year.

Despite it being a great proposal, I must admit I'm afraid of what PHP will become if we're adding features only to make them irrelevant three years later. I think we should be very careful and deliberate about how we're changing PHP, and not dismiss features that have only been in the language for a couple of years.

Now to be clear: I'm very well aware that asymmetric visibility and readonly properties aren't the same thing. Asymmetric visibility can do so much more. However: Nikita already coined a [very similar idea to asymmetric visibility](https://wiki.php.net/rfc/property_accessors) last year, which was denied in favour of readonly properties. Furthermore, I totally agree with [Marco's sentiment](https://externals.io/message/118353#118382) on the matter:

> I use readonly properties aggressively, and I try to make the state as immutable as possible.
>
> In the **extremely** rare cases where `<hljs keyword>public get`</hljs> and `<hljs keyword>private set</hljs>` are needed, I rely on traditional getters and setters, which are becoming extremely situational anyway, and still work perfectly fine.
> 
> […]
> 
> In fact, I'm writing so few getters and setters these days, that I don't see why I'd need getter and setter semantics to creep into the language, especially mutable ones, not even with the reflection improvements.

I would hate to see PHP become a language that throws out core features every couple of years, just for the sake of a little more flexibility. If we wanted more flexibility in this case, we should have decided on that two years ago when readonly properties were discussed in dept; now — in my opinion — is too late.

On a final note, if you are worried about cloning objects with new values (a problem this RFC would solve and readonly properties don't): people are already working on some kind of syntax to allow rewriting readonly properties while cloning. I'd say it's better to focus our efforts in that area, instead of coming up with an entirely different approach.

In summary: I think asymmetric visibility is a great feature for _some_ use cases, but I don't think it's worth adding it now that we have readonly properties. We decided on readonly properties, we'll have to stick with them for the sake of our users. I think **a unified vision and direction for PHP are lacking these days**, and this RFC — great as it is on its own — is a good example of that lacking in practice. I hope that we (PHP internals, that is) can come up with a solution, maybe the [PHP Foundation](https://opencollective.com/phpfoundation) has an important future role to play in all of this? 

{{ cta:like }}

{{ cta:mail }}

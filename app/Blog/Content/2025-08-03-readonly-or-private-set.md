---
title: 'Readonly or private(set)'
disableAds: true
meta:
    description: 'Which two should you choose when building struct-like objects in PHP?'
---

PHP is a mess. 

Let's just put that up front. I love it, but some parts are just… so frustrating. One good example is having to choose between readonly properties or properties that are privately writeable. I'll show you why.

Readonly properties were added in [PHP 8.1](/blog/php-81-readonly-properties). They allow a property to be set only once, and never change afterwards:

```php
final class Book
{
    public function __construct(
        public readonly string $title,
    ) {}
}
```

```php
$book = new Book('Timeline Taxi');

{:hl-error:$book->title = 'Timeline Taxi 2':};
{:hl-error-message:Cannot modify readonly property Book::$title:}
```

For the record, it's totally ok to construct an object without a value for a readonly property. PHP will only check a property's validity when reading it; that was part of the design of typed properties back in PHP 7.4:

```php
final class Book
{
    public readonly string $title;
}

$book = new Book();
```

```php
echo {:hl-error:$book->title:};
{:hl-error-message:Typed property Book::$title must not be accessed before initialization:}

// Setting a value after an object has been constructed is totally fine:
$book->title = 'Timeline Taxi';
```

Then [PHP 8.4](/blog/new-in-php-84#asymmetric-visibility-rfc) came along with "asymmetric visibility" which makes it possible to define a different property visibility (public, protected, or private), depending on what you're doing with that property: reading from it or writing to it — get or set operations. 

You could, for example have a `{:hl-keyword:private:}({:hl-keyword:set:})` property:

```php
final class Book
{
    public function __construct(
        private(set) string $title,
    ) {}
}
```

`{:hl-keyword:private:}({:hl-keyword:set:})` essentially means "this property is publicly readable but only privately writeable." It's actually a shorthand for `{:hl-keyword:public:} {:hl-keyword:private:}({:hl-keyword:set:})`. In other words, you can change the title of a book from within the class itself:

```php
final class Book
{
    public function __construct(
        private(set) string $title,
    ) {}
    
    public function markDraft(): self
    {
        // Perfectly fine to change the title from within the class itself
        $this->title .= ' (Draft)';
    
        return $this;
    }
}
```

But not from outside:

```php
$book = new Book('Timeline Taxi');

{:hl-error:$book->title .= ' (Draft)':};
{:hl-error-message:Cannot modify readonly property Book::$title:}
```

So why does all of this matter? These are two separate features — right? One is about preventing changes to properties once they've gotten a value, the other one is about restricting when that value can be changed.

When `{php}readonly` came along (three years before asymmetric visibility), many people used it to create so-called _data objects_; objects that represent data in a structured and typed way, which are then passed around all throughout you code. It's a very powerful pattern, and I [wrote about it back in 2018](/blog/structuring-unstructured-data) — in case you want to get some more background information. 

The addition of `{php}readonly` made it so that we could build classes with public properties, without having to add any getters or setters; these properties could never change after they had been created anyway, so why not skip all the boilerplate?

```php
final class Book
{
    public function __construct(
        public readonly string $title,
        public readonly Author $author,
        public readonly ChapterCollection $chapters,
        public readonly Publisher $publisher,
        public readonly null|DateTimeImmutable $publishedAt = null,
    ) {}
}
```

The practice got so popular that PHP 8.2 added a shorthand for "classes that only have readonly properties": 

```php
final readonly class Book
{
    public function __construct(
        public string $title,
        public Author $author,
        public ChapterCollection $chapters,
        public Publisher $publisher,
        public null|DateTimeImmutable $publishedAt = null,
    ) {}
}
```

But then came along PHP 8.4, with asymmetric visibility. And while it seems like it's a completely different feature, you could achieve the same result of "an object that can't be tampered with from the outside" by marking properties as `{:hl-keyword:private:}({:hl-keyword:set:})`:

```php
final class Book
{
    public function __construct(
        private(set) string $title,
        private(set) Author $author,
        private(set) ChapterCollection $chapters,
        private(set) Publisher $publisher,
        private(set) null|DateTimeImmutable $publishedAt = null,
    ) {}
}
```

You could make the case that properties with asymmetric visibility are better than readonly properties, because they still allow changes from within the class itself — it's a bit more flexible. 

On top of that, sometimes you do want to make changes to an object with readonly properties, but only by copying that data into a new object. Unfortunately, we don't have a proper `{:hl-keyword:clone with:}` expression in PHP to overwrite readonly properties while cloning, and the upcoming changes in PHP 8.5 to `{php}clone` actually don't address readonly properties correctly. It's a pretty deep rabbit hole, I made a video about it if you want to learn more:

<iframe width="560" height="345" src="https://www.youtube.com/embed/hkuy11kLlmM" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

But there's no denying: `{php}readonly` when used for data objects (so most of the time) is far less ideal compared to using asymmetric visibility. The problem is: `{php}readonly` has been in PHP for three years prior to asymmetric visibility, and is used in so many places. I noticed how newer code — [even within my own codebase](https://github.com/tempestphp/tempest-framework) — uses asymmetric visibility, while older places use readonly properties. This creates a lot of confusion, especially if you're maintaining open source code for people to use. There are semantic differences between `{php}readonly` and `{:hl-keyword:private:}({:hl-keyword:set:})`, especially if you're talking about cloning objects; they just happen to kind of both work for a very common use case.

So should I replace `{php}readonly` with `{:hl-keyword:private:}({:hl-keyword:set:})` everywhere it makes sense? Should `{php}readonly` maybe be deprecated in the future? Should I embrace the fact that `{php}readonly` came first and stick with it even though there's a better alternative? How do newcomers to PHP know which one to choose?

And the thing is… we saw this coming. The first time asymmetric visibility was pitched, I wrote about [how these two features clash and will confuse PHP developers for years to come](/blog/thoughts-on-asymmetric-visibility). And while I like asymmetric visibility more, the addition of it _after_ readonly properties is causing a lot of confusion — at least for me, maybe I'm the only one? [You should let me know](/discord)!

And you know the saddest part? All these features: readonly properties, readonly classes, asymmetric visibility, constructor property promotion — which I know didn't mention that's for another time; all these features are basically workarounds for something far more simple: having proper structs in PHP to represent data in a typed way.

```txt
{:hl-keyword:struct:} {:hl-type:Book:}
{
    {:hl-type:string:} {:hl-property:$title:};
    {:hl-type:Author:} {:hl-property:$author:};
    {:hl-type:ChapterCollection:} {:hl-property:$chapters:};
    {:hl-type:Publisher:} {:hl-property:$publisher:};
    {:hl-type:null|DateTimeImmutable:} {:hl-property:$publishedAt:} = {:hl-keyword:null:};
}
```

And yes, I know, the features I mentioned do a lot more than intermediate for structs, but I'm confident in saying that I would exchange all of them if we got proper structs in PHP.

So yeah, PHP is mess. It's a beautiful lovely mess, and I wouldn't want to change it for another language. 

But what a mess. 
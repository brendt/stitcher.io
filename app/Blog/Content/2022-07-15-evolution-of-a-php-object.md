---
title: 'The evolution of a PHP object throughout the years'
meta:
    template: blog/meta/evolution-of-php-object.twig
---

{{ ad:carbon }}

This is just a fun little post I wrote because I wanted to visualise how my [data transfer objects](/blog/structuring-unstructured-data) have evolved over the years.

If you prefer, you can watch my 2-minute as well:

<iframe width="560" height="422" src="https://www.youtube.com/embed/x9bSUo6TGgY" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

## August 2014: PHP 5.6

Let's start with PHP 5.6, this is what most people without modern-day PHP knowledge probably think PHP code still looks like. I'll just give you the code, and I'll mention what changes in future versions.

```php
class BlogData
{
    /** @var string */
    private $title;
    
    /** @var State */
    private $state;
    
    /** @var \DateTimeImmutable|null */
    private $publishedAt;
   
   /**
    * @param string $title 
    * @param State $state 
    * @param \DateTimeImmutable|null $publishedAt 
    */
    public function __construct(
        $title,
        $state,
        $publishedAt = null
    ) {
        $this->title = $title;
        $this->state = $state;
        $this->publishedAt = $publishedAt;
    }
    
    /**
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;    
    }
    
    /**
     * @return State 
     */
    public function getState() 
    {
        return $this->state;    
    }
    
    /**
     * @return \DateTimeImmutable|null 
     */
    public function getPublishedAt() 
    {
        return $this->publishedAt;    
    }
}
```

## December 2015: PHP 7.0

PHP 7.0 introduced some major new syntax features: scalar types and return types being the most notable here. Nullable types aren't a thing yet, so we still need to use doc block types for our nullable `$publishedAt`:

```php
class BlogData
{
    /** @var string */
    private $title;
    
    /** @var State */
    private $state;
    
    /** @var \DateTimeImmutable|null */
    private $publishedAt;
   
   /**
    * @param \DateTimeImmutable|null $publishedAt 
    */
    public function __construct(
        string $title,
        State $state,
        $publishedAt = null
    ) {
        $this->title = $title;
        $this->state = $state;
        $this->publishedAt = $publishedAt;
    }
    
    public function getTitle(): string
    {
        return $this->title;    
    }
    
    public function getState(): State 
    {
        return $this->state;    
    }
    
    /**
     * @return \DateTimeImmutable|null 
     */
    public function getPublishedAt() 
    {
        return $this->publishedAt;    
    }
}
```

## December 2016: PHP 7.1

With PHP 7.1 finally came nullable types, so we could remove some more doc blocks:

```php
class BlogData
{
    /** @var string */
    private $title;
    
    /** @var State */
    private $state;
    
    /** @var \DateTimeImmutable|null */
    private $publishedAt;
   
    public function __construct(
        string $title,
        State $state,
        ?DateTimeImmutable $publishedAt = null
    ) {
        $this->title = $title;
        $this->state = $state;
        $this->publishedAt = $publishedAt;
    }
    
    public function getTitle(): string
    {
        return $this->title;    
    }
    
    public function getState(): State 
    {
        return $this->state;    
    }
    
    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;    
    }
}
```

## November 2017: PHP 7.2

While there were some exciting features in 7.2 like parameter type widening and the `object` type, there's nothing we could do to clean up our specific DTO in this release.

## December 2018: PHP 7.3

The same goes for [PHP 7.3](/blog/new-in-php-73), nothing to see here.

## November 2019: PHP 7.4

[PHP 7.4](/blog/new-in-php-74) is a different story though! There now are [typed properties](/blog/typed-properties-in-php-74) — finally!

```php
class BlogData
{
    private string $title;
    
    private State $state;
    
    private ?DateTimeImmutable $publishedAt;
   
    public function __construct(
        string $title,
        State $state,
        ?DateTimeImmutable $publishedAt = null
    ) {
        $this->title = $title;
        $this->state = $state;
        $this->publishedAt = $publishedAt;
    }
    
    public function getTitle(): string
    {
        return $this->title;    
    }
    
    public function getState(): State 
    {
        return $this->state;    
    }
    
    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;    
    }
}
```

{{ cta:dynamic }}

## November 2020: PHP 8.0

Another game changer: [PHP 8](/blog/new-in-php-8) adds [promoted properties](/blog/constructor-promotion-in-php-8); also, trailing commas in parameter lists are now a thing!

```php
class BlogData
{
    public function __construct(
        private string $title,
        private State $state,
        private ?DateTimeImmutable $publishedAt = null,
    ) {}
    
    public function getTitle(): string
    {
        return $this->title;    
    }
    
    public function getState(): State 
    {
        return $this->state;    
    }
    
    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;    
    }
}
```

## November 2021: PHP 8.1

Next, we arrive at [PHP 8.1](/blog/new-in-php-81). Readonly properties are a thing, and allow us to write our DTO like so:


```php
class BlogData
{
    public function __construct(
        public readonly string $title,
        public readonly State $state,
        public readonly ?DateTimeImmutable $publishedAt = null,
    ) {}
}
```

## November 2022: PHP 8.2

And finally, we arrive at [PHP 8.2](/blog/new-in-php-82) — not released yet. 
Whenever a class only has readonly properties, the class itself can be marked as readonly, instead of every individual property:

```php
readonly class BlogData
{
    public function __construct(
        public string $title,
        public State $state,
        public ?DateTimeImmutable $publishedAt = null,
    ) {}
}
```

That's quite the difference, don't you think?

It's interesting to see how the language has evolved over the course of almost a decade. If you had proposed the 8.2 syntax 10 years ago, you'd probably be called a madman. The same is true [today](/blog/we-dont-need-runtime-type-checks), and I'm sure we'll look back at this point, ten years from now and wonder "how did we ever put up with that?".

{{ cta:like }}

{{ cta:mail }}

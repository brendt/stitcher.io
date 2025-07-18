Almost exactly one year ago, property hooks got [merged into PHP's core](https://github.com/php/php-src/pull/13455). If you've been following my work on [Tempest](https://tempestphp.com), you know that almost immediately after that happened, we started to prepare Tempest's codebase for PHP 8.4, and include property hooks wherever possible. I think it's fair to call myself an "early adopter", and I want to take a look back at how I've been using property hooks this past year. 

I realize many people aren't even running PHP 8.4 yet — let alone using property hooks. So this is probably a good time to get familiar with — what I would call — one of PHP's most impactful features of the decade.

<iframe width="560" height="345" src="https://www.youtube.com/embed/Y4B9QK1rXSM" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

## What are property hooks?

So, property hooks: one of [the biggest and most detailed RFCs](https://wiki.php.net/rfc/property-hooks) in PHP's history. It took some effort to [read through it all](https://www.youtube.com/watch?v=eyYeNE4NLMI), so let me summarize it for you. Property hooks allow you to "hook into" get and set access of a property. Think of it as the magic `{:hl-property:__get:}()` and `{:hl-property:__set:}()` methods, but specifically for one individual property:

```php
final class Book
{
    public function __construct(
        private array $authors,
    ) {}

    public string $credits {
        get {
            return implode(', ', array_map(
                fn (Author $author) => $author->name, 
                $this->authors,
            ));
        }
    }
    
    public Author $mainAuthor {
        set (Author $mainAuthor) {
            $this->authors[] = $mainAuthor;
            $this->mainAuthor = $mainAuthor;
        }
        
        get => $this->mainAuthor;
    }
}
```

Property hooks are amazing at reducing boilerplate getters and setters, not just from the perspective of the class itself, but also from the outside. 

Instead of writing this:

```php
$oldMainAuthor = $book->getMainAuthor();
$book->setMainAuthor($newMainAuthor);

echo $book->getCredits();
```

With property hooks, you can write this:

```php
$oldMainAuthor = $book->mainAuthor;
$book->mainAuthor = $newMainAuthor;

echo $book->credits;
```

Especially within the context of models, value objects, and data objects; property hooks make a lot of sense, and streamline a class' public API.

I did mention that the property hooks RFC was huge. There are a lot of details to it, like for example the shorthand syntax:

```php
final class Book
{
    public string $credits {
        get => implode(', ', array_map(
            fn (Author $author) => $author->name, 
            $this->authors,
        ));
    }

    // …
}
```

Or virtual properties — properties that only have _get_ access:

```php

final class Book
{
    public Author $mainAuthor {
        get => $this->authors[0];
    }
}
```

There's a lot to read about [references](https://wiki.php.net/rfc/property-hooks#references), [inheritance -](https://wiki.php.net/rfc/property-hooks#inheritance), and [type variance rules](https://wiki.php.net/rfc/property-hooks#property_type_variance) as well.

But the most important part of property hooks, far more impactful than anything else, is the fact that they can be defined on interfaces. It sounds weird — right? "Properties on interfaces". But it really makes sense. I'll show you.

## Properties on interfaces

Property hooks are essentially shorthands for regular getters and setters — which are methods. If you think about them this way, it makes sense that you need to be able to define them on interfaces, otherwise you'd still have to use regular getters and settings whenever you'd want to use an interface (which I think is a good practice). So instead of doing this:

```php
interface Book
{
    public function getChapters(): array;
    public function getMainAuthor(): Author;
    public function getCredits(): string;
}
```

You can write this:

```php
interface Book
{
    public array $chapters { get; }
    public Author $mainAuthor { get; }
    public string $credits { get; }
}
```

Whatever class implements the `{php}Book` interface, is now required to have publicly readable properties. You can still make them readonly or only privately writeable of course. You still have the safety of encapsulated objects, but without a bunch of boilerplate code:

```php
final class Ebook implements Book
{
    private(set) array $chapters;
    public readonly {:hl-type:Author:} $mainAuthor;
    public readonly {:hl-type:string:} $credits;
}
```

For comparison, if you'd want the same encapsulation safety but with regular getters and setters, your class would look like this:

```php
final class Ebook implements Book
{
    private array $chapters;
    private Author $mainAuthor;
    private string $credits;
    
    public function getChapters(): array
    {
        return $this->chatpers;
    }
    
    private function addChapter(Chapter $chapter): void
    {
        $this->chapters[] = $chapter;
    }
    
    public function getMainAuthor(): Author
    {
        return $this->mainAuthor;
    }
    
    public function getCredits(): string
    {
        return $this->credits;
    }
}
```

Honestly, I already got bored writing that one example. I can't even fathom anymore we always had to do this a year ago. 

Besides the argument that property hooks are essentially methods in disguise, there's another reason why they make a lot of sense to appear on interfaces: data and value objects. Over the years, PHP has been adding a lot of features that make it easier to write classes which represent data with properties alone: [typed properties](/blog/new-in-php-74#typed-properties-rfc), [readonly properties and classes](/blog/readonly-classes-in-php-82), and [constructor property promotion](/blog/constructor-promotion-in-php-8).

```php
final readonly class GenericRequest
{
    public function __construct(
        public Method $method,
        public string $uri,
        public array $headers,
        // …
    ) {}
}
```

However, the fact that properties couldn't be part of an interface, made all these previously added features rather limited — at least if you're like me and prefer to program to an interface. So you could say that the addition of property hooks immediately empowered a lot of other existing features in PHP as well. That's why I think it's the most impactful change of the past decade.

## A year later

So, what about my experience with property hooks? I use them a lot on interfaces. Even if this RFC didn't include anything besides "properties on interfaces", I would have been happy:

```php
interface Database
{
    public DatabaseDialect $dialect { get; }

    // …
}
```

```php
interface DatabaseConfig extends HasTag
{
    public string $dsn { get; }
    
    // …
}
```

```php
interface Request
{
    public Method $method { get; }
    public string $uri { get; }
    
    // …
}
```


The fact that you can "hook into a property" is nice though. I do use _get_ hooks kind of regularly. Virtual properties are useful in some cases, especially on models and data objects:

```php
final class PageVisited implements ShouldBeStored, HasCreatedAtDate
{
    // …
    
    public DateTimeImmutable $createdAt {
        get => $this->visitedAt;
    }
}
```

I don't have many use cases for _set_ hooks, in fact, throughout the whole of Tempest's codebase, we've only used one set hook:

```php

final class TestingCache implements Cache
{
    private Cache $cache;
    
    public bool $enabled {
        get => $this->cache->enabled;
        set => $this->cache->enabled = $value;
    }
    
    // …
}
```

They could make sense for "proxy objects" like this testing wrapper for caching, but there's honestly aren't that many cases for them.

The syntax itself is… ok. I don't like that there are multiple ways of doing the same thing: you have shorthand versions and an implicit `$value` variable for set hooks — it's kind of confusing to me. I'm a big fan of opinion-driven design, so I wouldn't have minded if there was one way and one way only to write hooks, but that's a nitpick.

<iframe width="560" height="345" src="https://www.youtube.com/embed/yBLVBwiAfrM" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

I also found myself writing property hooks _after_ the constructor. Which feels kind of weird at first because these are properties, but when you think about them as "methods in disguise", I think it makes sense.

```php
final class WelcomeEmail implements Email, HasAttachments
{
    public function __construct(
        private readonly User $user,
    ) {}

    public Envelope $envelope {
        get => new Envelope(
            subject: 'Welcome',
            to: $this->user->email,
        );
    }

    public string|View $html {
        get => view('welcome.view.php', user: $this->user);
    }
    
    public array $attachments {
        get => [
            Attachment::fromFilesystem(__DIR__ . '/welcome.pdf')
        ];
    }
}
```

In the end, property hooks are really cool and a game changer for how I write PHP. If you want to let me know your thoughts about them, you can find me [on Discord](/discord)!
---
title: 'My PHP enum style guide'
---

This is my personal style guide for using enums in PHP. Each section describes the rule, but also the personal, _opinionated_ reasoning behind the rule.

## 1. Uppercase

You must use uppercase notation for enum cases:

```php
enum PostState
{
    case PENDING;
    case PUBLISHED;
    case STARRED;
    case DENIED;
}
```

### Why?

Enums are very close to "constant values". Even though [the RFC](https://wiki.php.net/rfc/enumerations) showed them using PascalCase (inspired by other languages), it doesn't give a good reason for doing so. 

Some people argue to use PascalCase for enums specifically to visually distinguish between constants and enums. I don't believe that relying on naming conventions to distinguish between concepts is a good idea. It's better to rely on your IDE's or editor's insights to know what something is, instead of holding onto a naming convention.

Ironically, uppercase constants exist because of the same reason: to distinguish them from normal variables. I want to acknowledge that fact, however there's little we can do to change decades of programming history. I think it's fair to say that the majority of PHP projects use uppercase constants, and so I would like to keep following that convention for closely related concepts, despite how the convention originated.

Nevertheless, I have tried using PascalCase for enums, but it always felt unnatural to me: I wrote my enum cases uppercase automatically, only to notice I did afterwards; meaning I had to go back and refactor them. I decided not to fight my instincts and just go with uppercase notation which feels the most natural to me, because of how constant values have been written for years, and how close enums are to constants anyway.

{{ ad:carbon }}

## 2. Don't overuse backed enums

You should only use backed enums when you actually have a need for them. A common use case is serialization. If an enum is only going to be used within code, you shouldn't make it a backed enum. 

```php
enum PostState: string
{
    case PENDING = 'pending';
    case PUBLISHED = 'published';
    case STARRED = 'starred';
    case DENIED = 'denied';
}
```

### Why?

Manually assigning values to enums introduces maintenance overhead: you're not only maintaining the enum cases itself, but also their values. It's important to take into consideration when to add overhead, and when not.

On top of that: enums have their built-in `name` property that's always accessible in case you need a textual representation:

```php
Status::PUBLISHED->name; // PUBLISHED
```

When in doubt, apply this rule: do you need to create this enum from scalar values (from a database, from user input, …): use a backed enum. If the answer is "no" however, hold off assigning values until your use case requires it.

## 3. Simple match methods are allowed

Enums shouldn't contain complex methods. Ideally, they only contain methods using the `match` expression that provide richer functionality for specific enum cases:


```php
enum Status
{
    case DRAFT;
    case PUBLISHED;
    case ARCHIVED;

    public function label(): string
    {
        return match($this) {
            Status::DRAFT => 'work in progress',
            Status::PUBLISHED => 'published',
            Status::ARCHIVED => 'archived',
        };
    }
}
```

### Why?

Enums shouldn't provide complex functionality, if you find yourself writing too much logic in your enums, you probably want to look into using the [state pattern](/blog/laravel-beyond-crud-05-states) instead.

A good rule of thumb is that if it can fit into a `match` expression (without returning closures or callables or what not), you're good to go. Otherwise consider alternatives.

{{ cta:dynamic }}

## 4. Values shouldn't be used as labels

A backed enum's value should only be used as a value for (de)serialization, not for functionality like providing labels. 

### Why?

You might be tempted to rewrite the example from the previous point as follows:

```php
enum Status
{
    case DRAFT = 'work in progress';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
```

While shorter, you're now mixing two concerns into one: representing labels in a user-readable format, combined with their serialization value. The first one is very likely to change over time, the second one should stay unchanged as much as possible to prevent complex data migrations.

## 5. No index values

You should only use integer backed enums when their values actually are integers, not to assign some notion of "order" or "indexing" to your enums.

```php
enum VatPercentage: int 
{
    case SIX = 6;
    case TWELVE = 12;
    case TWENTY_ONE = 21;
}

enum Status: int 
{
    case DRAFT = 1;
    case PUBLISHED = 2;
    case ARCHIVED = 3;
}
```

### Why?

It might be tempting to assign integer values to, for example, status enums where `DRAFT` comes before `PUBLISHED`, comes before `ARCHIVED`.

While such sequential indexing works in a limited amount of cases, it's a mess to maintain. What happens when you're required to add another state called `DENIED`? It should probably get the value `2` or `3`, meaning that you're stuck with data migrations for all published and archived entries.

When in doubt, you can again use the state pattern to model complex transitions between states and their order instead.

---

Do you agree? Disagree? Let me know on [Twitter](https://twitter.com/brendt_gd) or via [email](mailto:brendt@stitcher.io)!

{{ cta:like }}

{{ cta:mail }}

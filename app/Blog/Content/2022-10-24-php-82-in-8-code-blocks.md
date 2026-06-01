---
title: 'PHP 8.2 in 8 code blocks'
next: new-in-php-82
footnotes:
    - { link: /blog/new-in-php-82, title: 'New in PHP 8.2', description: ' — A comprehensive list of all things new in PHP 8.2' }
    - { link: /blog/php-82-upgrade-mac, title: 'How to upgrade to PHP 8.2 on Mac' }
    - { link: /blog/readonly-classes-in-php-82, title: 'Readonly classes in PHP 8.2' }
    - { link: /blog/deprecated-dynamic-properties-in-php-82, title: 'Deprecated dynamic properties in PHP 8.2' }
    - { link: 'https://road-to-php.com/', title: 'The Road to PHP 8.2' }
---


{{ ad:carbon }}

```php
readonly class PostData
{
    public function __construct(
        public string $title,
        public string $author,
        public string $body,
        public DateTimeImmutable $createdAt,
        public PostState $state,
    ) {}
}
```

<em class="small center">[Readonly classes](/blog/readonly-classes-in-php-82)</em>

---

```php
$rng = $is_production
    ? new Random\Engine\Secure()
    : new Random\Engine\Mt19937(1234);
 
$randomizer = new Random\Randomizer($rng);

$randomizer->shuffleString('foobar');
```

<em class="small center">[New random extension](/blog/new-in-php-82#new-random-extension-rfc)</em>

---

```php
function alwaysFalse(): false
{
    return false;
}
```

<em class="small center">[`null`,  `true`, and `false` as standalone types](/blog/new-in-php-82#null,--true,-and-false-as-standalone-types-rfc)</em>

---

```php
function generateSlug((HasTitle&HasId)|null $post) 
{ /* … */ }
```

<em class="small center">[Disjunctive Normal Form Types](/blog/new-in-php-82#disjunctive-normal-form-types-rfc)</em>

---

```php
trait Foo 
{
    public const CONSTANT = 1;
 
    public function bar(): int 
    {
        return self::CONSTANT;
    }
}
```

<em class="small center">[Constants in traits](/blog/new-in-php-82#constants-in-traits-rfc)</em>

---

```php
function connect(
    string $user,
    #[\SensitiveParameter] string $password
) {
    // …
}
```

<em class="small center">[Redacted parameters](/blog/new-in-php-82#redact-parameters-in-back-traces-rfc)</em>

---

```php
class Post {}

$post = new Post();

$post->title = 'Name';

// Deprecated: Creation of dynamic property is deprecated
```

<em class="small center">[Deprecated dynamic properties](/blog/deprecated-dynamic-properties-in-php-82)</em>

---

```php
enum A: string 
{
    case B = 'B';
    
    const C = [self::B->value => self::B];
}
```

<em class="small center">[Enum properties in const expressions](/blog/new-in-php-82#fetch-properties-of-enums-in-const-expressions-rfc)</em>

---

{{ cta:dynamic }}
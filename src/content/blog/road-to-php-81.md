## The never type

```php
function redirect(<hljs type>string</hljs> $uri): never
{
    <hljs prop>header</hljs>("Location: {$uri}");
    
    exit;
}
```

## New in initializers

```php
class BlogData
{
    public function __construct(
        <hljs keyword>public readonly</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs> = '',
        <hljs keyword>public readonly</hljs> <hljs type>State</hljs> <hljs prop>$state</hljs>,
    ) {}
}
```

```php
class BlogData
{
    public function __construct(
        <hljs keyword>…</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs> = '',
        <hljs keyword>…</hljs> <hljs type>State</hljs> <hljs prop>$state</hljs> = <hljs keyword>new</hljs> <hljs type>Draft</hljs>(),
    ) {}
}
```

## Readonly properties

```php
class BlogData
{
    /** @var <hljs type>string</hljs> */
    private <hljs prop>$title</hljs>;
    
    /** @var <hljs type>State</hljs> */
    private <hljs prop>$state</hljs>;
    
    public function __construct(
        <hljs type>string</hljs> $title,
        <hljs type>State</hljs> $state
    ) {
        $this-><hljs prop>title</hljs> = $title;
        $this-><hljs prop>state</hljs> = $state;
    }
    
    public function getTitle(): string
    {
        return $this-><hljs prop>title</hljs>;    
    }
    
    public function getState(): State 
    {
        return $this-><hljs prop>state</hljs>;    
    }
}
```

```php
class BlogData
{
    private <hljs type>string</hljs> <hljs prop>$title</hljs>;
    
    private <hljs type>State</hljs> <hljs prop>$state</hljs>;
    
    public function __construct(
        <hljs type>string</hljs> $title,
        <hljs type>State</hljs> $state
    ) {
        $this-><hljs prop>title</hljs> = $title;
        $this-><hljs prop>state</hljs> = $state;
    }
    
    public function getTitle(): string
    {
        return $this-><hljs prop>title</hljs>;    
    }
    
    public function getState(): State 
    {
        return $this-><hljs prop>state</hljs>;    
    }
}
```

```php
class BlogData
{
    public function __construct(
        <hljs keyword>private</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>private</hljs> <hljs type>State</hljs> <hljs prop>$state</hljs>
    ) {}
    
    public function getTitle(): string
    {
        return $this-><hljs prop>title</hljs>;    
    }
    
    public function getState(): State 
    {
        return $this-><hljs prop>state</hljs>;    
    }
}
```

```php
class BlogData
{
    public function __construct(
        <hljs keyword>public readonly</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>public readonly</hljs> <hljs type>State</hljs> <hljs prop>$state</hljs>
    ) {}
}
```

## Pure intersection types

```php
function test(<hljs type>A</hljs>|<hljs type>B</hljs> $foo) { /* … */ }
```

```php
function test(<hljs type>A</hljs>&<hljs type>B</hljs> $foo) { /* … */ }
```

```php
interface WithUuid
{
    public function getUuid(): Uuid;
}

interface WithSlug
{
    public function getSlug(): string;
}
```

```php
function url($object): string { /* … */ }
```

```php
interface WithUrl extends WithUuid, WithSlug
{}
```

```php
function url(<hljs type>WithUuid</hljs>&<hljs type>WithSlug</hljs> $object): <hljs type>string</hljs> 
{ /* … */ }
```

## Array unpacking

```php
$arrayA = [1, 2, 3];

$arrayB = [4, 5];

$result = [0, ...$arrayA, ...$arrayB, 6, 7];

// [0, 1, 2, 3, 4, 5, 6, 7]
```

```php
$arrayA = ['a' => 1];

$arrayB = ['b' => 2];

$result = ['a' => 0, ...$arrayA, ...$arrayB];

// ['a' => 1, 'b' => 2]
```

## First-class callables

```php
$foo = [$this, 'foo'];

$strlen = <hljs type>Closure</hljs>::<hljs prop>fromCallable</hljs>('strlen');
```

```php
$foo = $this-><hljs prop>foo</hljs>(...);

$strlen = <hljs prop>strlen</hljs>(...)
```

```php
$foo = <hljs type>MyClass</hljs>::<hljs prop>foo</hljs>(...);
```

```php
class MyController
{
    public function index() { /* … */ }
}

$action = <hljs striped><hljs type>MyController</hljs>::<hljs prop>index</hljs>(...)</hljs>;
```

---

## Enums

```php
/**
 * @method static self <hljs prop>draft</hljs>() 
 * @method static self <hljs prop>published</hljs>() 
 * @method static self <hljs prop>archived</hljs>() 
 */
class StatusEnum extends Enum {}
```

```php
<hljs keyword>enum</hljs> <hljs type>Status</hljs> {
    case <hljs prop>draft</hljs>;
    case <hljs prop>published</hljs>;
    case <hljs prop>archived</hljs>;
}
```

```php
<hljs keyword>enum</hljs> <hljs type>Status</hljs> {
    case <hljs prop>draft</hljs>;
    case <hljs prop>published</hljs>;
    case <hljs prop>archived</hljs>;
    
    public function color(): string
    {
        return <hljs keyword>match</hljs>($this) {
            <hljs type>Status</hljs>::<hljs prop>draft</hljs> = 'grey',
            <hljs type>Status</hljs>::<hljs prop>published</hljs> = 'green',
            <hljs type>Status</hljs>::<hljs prop>archived</hljs> = 'red',
        };
    }
}
```

```php
<hljs keyword>enum</hljs> <hljs type>Status</hljs>: <hljs type>string</hljs> {
    case <hljs prop>draft</hljs> = 'draft';
    case <hljs prop>published</hljs> = 'published';
    case <hljs prop>archived</hljs> = 'archived';
}
```

```php
class BlogPost
{
    public function __construct(
        <hljs keyword>public</hljs> <hljs type>Status</hljs> <hljs prop>$status</hljs>,
    ) {}
}
```

```php
$post = new <hljs type>BlogPost</hljs>(<hljs type>Status</hljs>::<hljs prop>draft</hljs>);
```

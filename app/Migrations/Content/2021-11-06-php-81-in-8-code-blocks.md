{{ ad:carbon }}

```php
<hljs keyword>enum</hljs> <hljs type>Status</hljs>
{
    case <hljs prop>draft</hljs>;
    case <hljs prop>published</hljs>;
    case <hljs prop>archived</hljs>;
    
    public function color(): string
    {
        return <hljs keyword>match</hljs>($this) 
        {
            <hljs type>Status</hljs>::<hljs prop>draft</hljs> => 'grey',   
            <hljs type>Status</hljs>::<hljs prop>published</hljs> => 'green',   
            <hljs type>Status</hljs>::<hljs prop>archived</hljs> => 'red',   
        };
    }
}
```

<em class="small center">[Enums](/blog/php-enums)</em>

---

```php
class PostData
{
    public function __construct(
        <hljs keyword>public</hljs> <hljs keyword>readonly</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>public</hljs> <hljs keyword>readonly</hljs> <hljs type>string</hljs> <hljs prop>$author</hljs>,
        <hljs keyword>public</hljs> <hljs keyword>readonly</hljs> <hljs type>string</hljs> <hljs prop>$body</hljs>,
        <hljs keyword>public</hljs> <hljs keyword>readonly</hljs> <hljs type>DateTimeImmutable</hljs> <hljs prop>$createdAt</hljs>,
        <hljs keyword>public</hljs> <hljs keyword>readonly</hljs> <hljs type>PostState</hljs> <hljs prop>$state</hljs>,
    ) {}
}
```

<em class="small center">[Readonly properties](/blog/php-81-readonly-properties)</em>

---

```php
class PostStateMachine
{
    public function __construct(
        <hljs keyword>private</hljs> <hljs type>State</hljs> <hljs prop>$state</hljs> = <hljs keyword>new</hljs> <hljs type>Draft</hljs>(),
    ) {
    }
}
```

<em class="small center">[New in initializers](/blog/php-81-new-in-initializers)</em>

---

```php
$fiber = new <hljs type>Fiber</hljs>(function (): <hljs type>void</hljs> {
    $valueAfterResuming = <hljs type>Fiber</hljs>::<hljs prop>suspend</hljs>('after suspending');
    
    // … 
});
 
$valueAfterSuspending = $fiber-><hljs prop>start</hljs>();
 
$fiber-><hljs prop>resume</hljs>('after resuming');
```


<em class="small center">[Fibers, a.k.a. "green threads"](/blog/fibers-with-a-grain-of-salt)</em>

{{ cta:dynamic }}

```php
$array1 = ["a" => 1];

$array2 = ["b" => 2];

$array = ["a" => 0, ...$array1, ...$array2];

<hljs prop>var_dump</hljs>($array); // ["a" => 1, "b" => 2]
```

<em class="small center">[Array unpacking also supports string keys](/blog/new-in-php-81#array-unpacking-with-string-keys-rfc)</em>

---

```php
function foo(<hljs type>int</hljs> $a, <hljs type>int</hljs> $b) { /* … */ }

$foo = <hljs prop>foo</hljs>(...);

$foo(<hljs prop>a:</hljs> 1, <hljs prop>b:</hljs> 2);
```

<em class="small center">[First class callables](https://wiki.php.net/rfc/first_class_callable_syntax)</em>

---

```php
function generateSlug(<hljs type>HasTitle</hljs>&<hljs type>HasId</hljs> $post) {
    return <hljs prop>strtolower</hljs>($post-><hljs prop>getTitle</hljs>()) . $post-><hljs prop>getId</hljs>();
}
```

<em class="small center">[Pure intersection types](/blog/new-in-php-81#pure-intersection-types-rfc)</em>

---

```php
$list = ["a", "b", "c"];

<hljs prop>array_is_list</hljs>($list); // true

$notAList = [1 => "a", 2 => "b", 3 => "c"];

<hljs prop>array_is_list</hljs>($notAList); // false

$alsoNotAList = ["a" => "a", "b" => "b", "c" => "c"];

<hljs prop>array_is_list</hljs>($alsoNotAList); // false
```

<em class="small center">[The new `array_is_list` function](/blog/new-in-php-81#new-array_is_list-function-rfc)</em>

{{ cta:like }}

{{ cta:mail }}


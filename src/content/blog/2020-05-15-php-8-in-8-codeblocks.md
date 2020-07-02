PHP 8 brings lots of new features, in this list we'll look at the most outstanding ones. If you want a full list and background information, you can read about all things [new in PHP 8](/blog/new-in-php-8).

{{ ad:carbon }}

--- 

```php
use <hljs type>\Support\Attributes\ListensTo</hljs>;

class ProductSubscriber
{
    @@<hljs type green>ListensTo</hljs>(<hljs type>ProductCreated</hljs>::class)
    public function onProductCreated(<hljs type>ProductCreated</hljs> $event) { /* … */ }

    @@<hljs type green>ListensTo</hljs>(<hljs type>ProductDeleted</hljs>::class)
    public function onProductDeleted(<hljs type>ProductDeleted</hljs> $event) { /* … */ }
}
```

Attributes — aka annotations — you can read about them in depth in [this post](/blog/attributes-in-php-8).

---

```php
public function foo(<hljs type green>Foo|Bar</hljs> $input): <hljs type green>int|float</hljs>;

public function bar(<hljs type green>mixed</hljs> $input): <hljs type green>mixed</hljs>;
```

Union types allows for type hinting several types. There's also a new `mixed` type which represents [several types](/blog/new-in-php-8#new-mixed-type-rfc) at once.

---

```php
interface Foo
{
    public function bar(): <hljs type green>static</hljs>;
}
```

The `static` return type is built-in.

---

```ini
[JIT]
<hljs keyword green>opcache.jit</hljs>=5
```

[The just-in-time compiler](/blog/php-jit) for PHP.

---

```php
$triggerError = <hljs keyword>fn</hljs>() <hljs green>=> throw</hljs> new <hljs type>MyError</hljs>();

$foo = $bar['offset'] <hljs green>?? throw</hljs> new <hljs type>OffsetDoesNotExist</hljs>('offset');
```

`throw` can be used in expressions.

---

```php
try {
    // Something goes wrong
} catch (<hljs type green>MySpecialException</hljs>) {
    Log::error("Something went wrong");
}
```

Non-capturing catches: no need to specify an exception variable if you don't need it.

---

```php
public function(
    <hljs type>string</hljs> $parameterA,
    <hljs type>int</hljs> $parameterB,
    <hljs type>Foo</hljs> $objectfoo<hljs green>,</hljs>
) {
    // …
}
```

Trailing commas are allowed in parameter lists

---

```
<hljs prop green>str_contains</hljs>('string with lots of words', 'words');

<hljs prop green>str_starts_with</hljs>('haystack', 'hay');

<hljs prop green>str_ends_with</hljs>('haystack', 'stack');
```

New string functions.

--- 

Let's not fool ourselves: 8 code blocks isn't enough to summarise all great new things in PHP 8. So let's just add a few more.

```php
function bar(<hljs type green>Stringable</hljs> $stringable) { /* … */ }
```

A new [`Stringable` interface](/blog/new-in-php-8#new-stringable-interface-rfc).

---

```php
$object<hljs green>::<hljs keyword>class</hljs></hljs>
```

Call `::class` directly on objects.

---

There's even more. If you want a full list, you can find it [on this blog](/blog/new-in-php-8).

What feature are you looking forward to the most? Let me know on [Twitter](*https://twitter.com/brendt_gd) or via [email](mailto:brendt@stitcher.io).

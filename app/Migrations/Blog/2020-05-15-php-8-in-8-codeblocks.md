PHP 8 brings lots of new features, in this list we'll look at the most outstanding ones. If you want a full list and background information, you can read about all things [new in PHP 8](/blog/new-in-php-8).

{{ ad:carbon }}

---

```php
use <hljs type>Support\Attributes\ListensTo</hljs>;

class ProductSubscriber
{
    #[<hljs type green>ListensTo</hljs>(<hljs type>ProductCreated</hljs><hljs keyword>::class</hljs>)]
    public function onProductCreated(<hljs type>ProductCreated</hljs> $event) { /* … */ }

    #[<hljs type green>ListensTo</hljs>(<hljs type>ProductDeleted</hljs><hljs keyword>::class</hljs>)]
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
<hljs keyword green>opcache.jit</hljs>=1225
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
<hljs prop>setcookie</hljs>(
    <hljs prop>name</hljs>: 'test',
    <hljs prop>expires</hljs>: <hljs prop>time</hljs>() + 60 * 60 * 2,
);
```

[Named arguments](/blog/php-8-named-arguments).

---

```php
$result = <hljs keyword>match</hljs>($input) {
    0 => "hello",
    '1', '2', '3' => "world",
};
``` 

The `match` expression as an improvement to the `switch` expression.

{{ cta:dynamic }}

There's even more. If you want a full list, you can find it [on this blog](/blog/new-in-php-8).

What feature are you looking forward to the most? Let me know on [Twitter](*https://twitter.com/brendt_gd) or via [email](mailto:brendt@stitcher.io).

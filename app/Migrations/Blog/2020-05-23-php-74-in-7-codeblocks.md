PHP 7.4, the last edition in the 7.* series, brings lots of new and handy changes. This post lists the highlights, though there's much more to this release. You can read all about the full release in this post about [what's new in PHP 7.4](/blog/new-in-php-74).

{{ ad:carbon }}

---

```php
<hljs prop>array_map</hljs>(
    <hljs green><hljs keyword>fn</hljs>(<hljs type>User</hljs> $user) => $user->id</hljs>,
    $users
);
```

Arrow functions, a.k.a. short closures. You can read about them in depth in [this post](/blog/short-closures-in-php).

---

```php
class A
{
    public <hljs green type>string</hljs> $name;
    
    public <hljs green type>?Foo</hljs> $foo;
}
```

Type properties. There's quite a lot to tell [about them](/blog/typed-properties-in-php-74).

---

```php
$data['date'] <hljs green>??=</hljs> new <hljs type>DateTime</hljs>();
```

The null coalescing assignment operator. If you're unfamiliar with the null coalescing operator, you can read all about [shorthand operators](/blog/shorthand-comparisons-in-php) in this blog.

---

```php
class ParentType {}
class ChildType extends ParentType {}

class A
{
    public function covariantReturnTypes(): <hljs green type>ParentType</hljs>
    { /* … */ }
}

class B extends A
{
    public function covariantReturnTypes(): <hljs green type>ChildType</hljs>
    { /* … */ }
}
```

Improved type variance. If you're not sure what that's about, you should take a look at this post about [Liskov and type safety](/blog/liskov-and-type-safety).

---

```php
$result = [<hljs green>...</hljs>$arrayA, <hljs green>...</hljs>$arrayB];
```

The array spread operator. There are a few [sidenotes](/blog/new-in-php-74#array-spread-operator-rfc) to be made about them.

---

```php
$formattedNumber = 107<hljs green>_</hljs>925<hljs green>_</hljs>284.88;
```

The numeric literal separator, which is only a visual aid.

---

```ini
[preloading]
<hljs keyword>opcache.preload</hljs>=/path/to/project/preload.php
```

Preloading improves PHP performance across requests. It's a complicated topic, but I wrote about it [here](/blog/preloading-in-php-74).

{{ cta:mail }}

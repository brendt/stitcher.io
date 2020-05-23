PHP 7.4, the last edition in the 7.* series, brings lots of new and handy changes. This post lists the highlights, though there's much more to this release. You can read all about the full release in this post about [what's new in PHP 7.4](/blog/new-in-php-74).

{{ ad:carbon }}

---

```php
<hljs prop>array_map</hljs>(
    <hljs green><hljs keyword>fn</hljs>(<hljs type>User</hljs> $user) => $user->id</hljs>,
    $users
);
```

Arrow functions, a.k.a. short closures.

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
$data['date'] <hljs green>??=</hljs> new DateTime();
```

The null coalescing assignment operator.

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

The array spread operator.

---

```php
$formattedNumber = 107<hljs green>_</hljs>925<hljs green>_</hljs>284.88;
```

The numeric literal separator.

---

```ini
[preloading]
<hljs keyword>opcache.preload</hljs>=/path/to/project/preload.php
```

Preloading improves PHP performance across requests.

---

There's even more. If you want a full list, you can find it [on this blog](/blog/new-in-php-74).
What 7.4 feature do you like the most? Let me know on [Twitter](*https://twitter.com/brendt_gd) or via [email](mailto:brendt@stitcher.io).

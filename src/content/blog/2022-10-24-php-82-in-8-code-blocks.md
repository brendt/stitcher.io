
{{ ad:carbon }}

```php
<hljs keyword>readonly</hljs> class PostData
{
    public function __construct(
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$author</hljs>,
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$body</hljs>,
        <hljs keyword>public</hljs> <hljs type>DateTimeImmutable</hljs> <hljs prop>$createdAt</hljs>,
        <hljs keyword>public</hljs> <hljs type>PostState</hljs> <hljs prop>$state</hljs>,
    ) {}
}
```

<em class="small center">[Readonly classes](/blog/new-in-php-82#readonly-classes-rfc)</em>

---

```php
$rng = $is_production
    ? new <hljs type>Random\Engine\Secure</hljs>()
    : new <hljs type>Random\Engine\Mt19937</hljs>(1234);
 
$randomizer = new <hljs type>Random\Randomizer</hljs>($rng);

$randomizer-><hljs prop>shuffleString</hljs>('foobar');
```

<em class="small center">[New random extension](/blog/new-in-php-82#new-random-extension-rfc)</em>

---

```php
function alwaysFalse(): <hljs type>false</hljs>
{
    return false;
}
```

<em class="small center">[`<hljs type>null</hljs>`,  `<hljs type>true</hljs>`, and `<hljs type>false</hljs>` as standalone types](/blog/new-in-php-82#null,--true,-and-false-as-standalone-types-rfc)</em>

---

```php
function <hljs prop>generateSlug</hljs>((<hljs type>HasTitle</hljs>&<hljs type>HasId</hljs>)|<hljs type>null</hljs> $post) 
{ /* … */ }
```

<em class="small center">[Disjunctive Normal Form Types](/blog/new-in-php-82#disjunctive-normal-form-types-rfc)</em>

---

```php
trait <hljs type>Foo</hljs> 
{
    public const <hljs prop>CONSTANT</hljs> = 1;
 
    public function bar(): int 
    {
        return self::<hljs prop>CONSTANT</hljs>;
    }
}
```

<em class="small center">[Constants in traits](/blog/new-in-php-82#constants-in-traits-rfc)</em>

---

```php
function connect(
    <hljs type>string</hljs> $user,
    #[<hljs type>\SensitiveParameter</hljs>] <hljs type>string</hljs> $password
) {
    // …
}
```

<em class="small center">[Redacted parameters](/blog/new-in-php-82#redact-parameters-in-back-traces-rfc)</em>

---

```php
class Post {}

$post = new <hljs type>Post</hljs>();

$post-><hljs striped>title</hljs> = 'Name';

// Deprecated: Creation of dynamic property is deprecated
```

<em class="small center">[Deprecated dynamic properties](/blog/deprecated-dynamic-properties-in-php-82)</em>

---

```php
<hljs keyword>enum</hljs> <hljs type>A</hljs>: <hljs type>string</hljs> 
{
    case <hljs prop>B</hljs> = 'B';
    
    const <hljs prop>C</hljs> = [<hljs yellow>self::<hljs prop>B</hljs>-><hljs prop>value</hljs></hljs> => self::<hljs prop>B</hljs>];
}
```

<em class="small center">[Enum properties in const expressions](/blog/new-in-php-82#fetch-properties-of-enums-in-const-expressions-rfc)</em>

---

{{ cta:dynamic }}
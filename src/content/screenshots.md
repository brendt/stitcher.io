
```php
$collection = new <hljs type>PostCollection</hljs>();

$collection[] = new <hljs type>Post</hljs>(1);

<hljs striped>$collection[] = 'abc';</hljs>

foreach ($collection as $item) {
    echo $item-><hljs prop>getId</hljs>();
}
```

```php
class PostCollection extends ArrayIterator
{
    public function current() : ?<hljs type>Post</hljs> { /* … */ }

    public function offsetGet(<hljs type>mixed</hljs> $key) : ?<hljs type>Post</hljs> { /* … */ }

    public function offsetSet(<hljs type>mixed</hljs> $key, <hljs type>mixed</hljs> $value): void
    {
        if (! $value instanceof <hljs type>Post</hljs>) {
            throw new <hljs type>InvalidArgumentException</hljs>("…");
        }
        
        // …
    }
}
```



```php
$posts = [];

$posts[] = 1;
$posts[] = 'string';
```

```php
$posts = [];
```

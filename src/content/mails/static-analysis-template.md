## Hello World

<div class="quote">

If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a di
If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a di

 If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a di
</div>

```txt
<hljs prop>parameters</hljs>:
    <hljs prop>level</hljs>: 6
    <hljs prop>paths</hljs>:
        - src
        - tests
```

##### phpstan.neon


```php
class Collection<<hljs type>GenericType</hljs>> 
    implements <hljs type>ArrayAccess</hljs>, <hljs type>Iterator</hljs>
{
    public function <hljs prop>__construct</hljs>(
        <hljs keyword>private array</hljs> <hljs prop>$items</hljs> = [],
    ) {}
    
    public function offsetGet($offset): <hljs type>GenericType</hljs> {
        return <hljs prop>array_key_exists</hljs>($offset, $this-><hljs prop>items</hljs>)
            ? $this-><hljs prop>items</hljs>[$offset]
            : throw new <hljs type>UnknownItem</hljs>;
    }
    
    public function offsetSet(
        $offset, 
        <hljs type>GenericType</hljs> $value
    ): <hljs type>void</hljs> {
        $this-><hljs prop>items</hljs>[$offset] = $value;
    }
    
    // …
}
```

In this series, we'll mainly look at Psalm and PHPStan though, and we'll dedicate one mail to Phan. For me, there's two main differences between Psalm and PHPStan, but they are highly subjective.

I personally like Psalm's config and tooling better than PHPStan. Psalm uses a proper XML scheme, which allows an IDE like PhpStorm to actually autocomplete config options. PHPStan uses neon files which — to be honest — I never heard of before using PHPStan. This is definitely not a deal-breaker, but it is something I noticed when switching between Psalm and PHPStan.

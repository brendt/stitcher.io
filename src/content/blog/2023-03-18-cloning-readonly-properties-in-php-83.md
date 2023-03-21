PHP 8.3 adds the possibility of overwriting readonly property values while cloning an object. Don't be mistaken though: you're not able to clone any object and overwrite their readonly values from any place. This feature only addresses a very specific (but important) edge case.

Let's take a look!

{{ ad:carbon }}

In an ideal world, we'd be able to clone classes with readonly properties, based on a user-defined set of values. The so called `<hljs keyword>clone with</hljs>` syntax (which doesn't exist):

```php
<hljs keyword>readonly</hljs> class Post
{
    public function __construct(
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$author</hljs>,
        <hljs keyword>public</hljs> <hljs type>DateTime</hljs> <hljs prop>$createdAt</hljs>,
    ) {}
}

$post = new <hljs type>Post</hljs>(
    <hljs prop>title</hljs>: 'Hello World',
    // …
);

 // This isn't possible!
$updatedPost = clone $post <hljs keyword>with</hljs> {
    <hljs prop>title</hljs>: 'Another One!',
};
```

Reading the title of the current [RFC](https://wiki.php.net/rfc/readonly_amendments): "Readonly properties can be reinitialized during cloning" — you might think something like `<hljs keyword>clone with</hljs>` this is now possible. However… it isn't. The RFC allows only allows for one specific operation: to overwrite readonly values in the magic `<hljs prop>__clone</hljs>` method:


```php
<hljs keyword>readonly</hljs> class Post
{
    public function __construct(
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$author</hljs>,
        <hljs keyword>public</hljs> <hljs type>DateTime</hljs> <hljs prop>$createdAt</hljs>,
    ) {}
    
    public function __clone()
    {
        $this-><hljs prop>createdAt</hljs> = new <hljs type>DateTime</hljs>(); 
        // This is allowed,
        // even though `createdAt` is a readonly property.
    }
}
```

Is this useful? It is! Say you want to clone objects with nested objects — a.k.a. making "deep clones"; then this RFC allows you to clone those nested objects as well, and overwrite them in your newly created clone — even when they are readonly properties.

```php
<hljs keyword>readonly</hljs> class Post
{
    public function __clone()
    {
        $this-><hljs prop>createdAt</hljs> = clone $this-><hljs prop>createdAt</hljs>; 
        // Creates a new DateTime object,
        // instead of reusing the reference
    }
}
```

Without this RFC, you'd be able to clone `$post`, but it would still hold a reference to the original `$createdAt` object. Say you'd make changes to that object (which is possible since `<hljs keyword>readonly</hljs>` only prevents the property assigned from changing, not from its inner values being changed):

```php
$post = new <hljs type>Post</hljs>(/* … */);

$otherPost = clone $post;

$post-><hljs prop>createdAt</hljs>-><hljs prop>add</hljs>(new <hljs type>DateInterval</hljs>('P1D'));

$otherPost-><hljs prop>createdAt</hljs> === $post-><hljs prop>createdAt</hljs>; // true :(
```

Then you'd end up with the `$createdAt` date changed on both objects!

Thanks to this RFC, we can make real clones, with all their nested properties cloned as well, even when these properties are readonly:


```php
$post = new <hljs type>Post</hljs>(/* … */);

$otherPost = clone $post;

$post-><hljs prop>createdAt</hljs>-><hljs prop>add</hljs>(new <hljs type>DateInterval</hljs>('P1D'));

$otherPost-><hljs prop>createdAt</hljs> === $post-><hljs prop>createdAt</hljs>; // false :)
```

## On a personal note

I think it's good that PHP 8.3 makes it possible deep cloning readonly properties. However, I _have_ mixed feelings about this implementation. Imagine for a second that `<hljs keyword>clone with</hljs>` existed in PHP, then all of the above would have been unnecessary. Take a look:

```php
// Again, this isn't possible!
$updatedPost = clone $post <hljs keyword>with</hljs> { 
    <hljs prop>createdAt</hljs>: clone $post-><hljs prop>createdAt</hljs>,
};
```

Now imagine that `<hljs keyword>clone with</hljs>` gets added in PHP 8.4 — pure speculation, of course. It means we'd have two ways of doing the same thing in PHP. I don't know about you, but I don't like it when languages or frameworks offer [several ways of doing the same thing](https://www.youtube.com/watch?v=yBLVBwiAfrM&ab_channel=CodeStyle). As far as I'm concerned, that's suboptimal language design at best.

This is, of course, assuming that `<hljs keyword>clone with</hljs>` would be able to automatically map values to properties without the need of manually implementing mapping logic in `<hljs prop>__clone</hljs>`. I'm also assuming that `<hljs keyword>clone with</hljs>` can deal with property visibility: only able to change public properties from the outside, but able to change protected and private properties when used inside a class.

Awhile ago, I wrote about [how PHP internals seem to be divided](/blog/thoughts-on-asymmetric-visibility), one group comes up with one solution, while another group wants to take another approach. To me, it's a clear drawback of [designing by committee](https://marketoonist.com/2017/10/committee.html).

Full disclosure — the RFC mentions `<hljs keyword>clone with</hljs>` as a future scope: 

> None of the envisioned ideas for the future collide with the proposals in this RFC. They could thus be considered separately later on.
 
But I tend to disagree with this statement, at least assuming that `<hljs keyword>clone with</hljs>` would work without having to implement any userland code. If we'd follow the trend of this current RFC, I could imagine someone suggesting to add `<hljs keyword>clone with</hljs>` only as a way to pass data into `<hljs prop>__clone</hljs>`, and have users deal with it themselves:

```php
<hljs keyword>readonly</hljs> class Post
{
    public function __clone(...$properties)
    {
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }
    }
}
```

However, I really hope this isn't the way `<hljs keyword>clone with</hljs>` gets implemented; because you'd have to add a `<hljs prop>__clone</hljs>` implementation on every readonly class. 

So, assuming the best case where `<hljs keyword>clone with</hljs>` gets added, and where it is able to automatically map values; then the functionality of this current RFC gets voided, and makes it so that we have two ways of doing the same thing. It will confuse users because it faces them with yet another a decision when coding. I think PHP has grown confusing enough as is, and I'd like to see that change.

On the other hand, I do want to mention that I don't oppose this RFC on its own. I think Nicolas and Máté did a great job coming up with a solid solution to a real-life problem. 

--- 

PS: in case someone wants to make the argument for the current RFC because you only need to implement `<hljs prop>__clone</hljs>` once per object and not worry about it on call-site anymore. There's one very important details missing from these examples in isolation: deep copying doesn't happen with a simple `<hljs keyword>clone</hljs>` call. Most of the time, packages like [deep-copy](https://packagist.org/packages/myclabs/deep-copy) are used, and thus, the potential overhead that comes with my `<hljs keyword>clone with</hljs>` example is already taken care off by those packages and don't bother end users.
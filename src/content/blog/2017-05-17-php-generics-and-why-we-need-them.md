In today's blog post we'll explore some common problems with arrays in PHP. All the problems and issues listed could be solved with a pending RFC which adds generics to PHP. We won't explore in too much detail what generics are, but at the end of this read, you should have a good idea as to why they are useful, and why we really want them in PHP. So without further ado, let's dive into the subject.

{{ ad:carbon }}

Imagine you have a collection of blog posts, loaded from a data source.

```php
$posts = $blogModel-><hljs prop>find</hljs>();
```

Now you want to loop over every post, and do *something* with its data; let's say, the `id`.

```php
foreach ($posts as $post) {
    $id = $post-><hljs prop>getId</hljs>();
    
    // Do something
}
```

This is a scenario that happens often. 
And it's this scenario we'll explore to discuss why generics are awesome, 
and why the PHP community desperately needs them.
 
Let's take a look at the problems of the above approach.

## Data integrity

In PHP, an array is a collection of… things.

```php
$posts = [
    'foo',
    null,
    self::<hljs prop>BAR</hljs>,
    new <hljs type>Post</hljs>('Lorem'),
];
```

Looping over this array of posts would result in a fatal error.

```txt
<hljs error full>PHP Fatal error:  Uncaught Error: 
Call to a member function getId() on string</hljs>
```

We're calling `-><hljs prop>getId</hljs>()` on the string `'foo'`. Not done. When looping over an array, we want to be sure that 
 every value is of a certain type. We could do something like this.
 
```php
foreach ($posts as $post) {
    if (! $post instanceof <hljs type>Post</hljs>) {
        continue;
    }

    $id = $post-><hljs prop>getId</hljs>();
    
    // Do something
}
```

This would work, but if you've written some production PHP code, you know these checks can grow quickly, and pollute 
 the codebase. In our example, we could verify the type of each entry in the `->find()` method on `$blogModel`.
 However, that's just moving the problem from one place to another. It's a bit better though.
 
There's another problem with data integrity. Say you have a method which requires an array of `Post`s.

```php
function handlePosts(<hljs type>array</hljs> $posts) {
    foreach ($posts as $post) {
        // ...
    }
}
```

Again, we could add extra checks in this loop, but we could not guarantee that `$posts` only holds a collection of `<hljs type>Post</hljs>` objects.

[As of PHP 7.0](*http://php.net/manual/en/functions.arguments.php#functions.variable-arg-list), you could use the `...` operator to work around this issue.

```php
function handlePosts(<hljs type>Post</hljs> ...$posts) {
    foreach ($posts as $post) {
        // ...
    }
}
```

But the downside of this approach: you would have to call the function with an unpacked array.

```php
<hljs prop>handlePosts</hljs>(...$posts);
```

{{ cta:mail }}

## Performance

You can imagine it's better to know beforehand whether an array contains only elements of a certain type, rather then
 manually checking the types within a loop, every, single, time.
 
We can't do benchmarks on generics, because they don't exist yet, so it's only guessing as to how they would impact performance.
 It's not far-fetched to assume though, that PHP's optimised behaviour, written in C; is a better way to solve the problem than
 to write lots of userland code.


 
## Code completion

I don't know about you, but I use an IDE when writing PHP code. Code completion increases productivity immensely, so I'd also 
 like to use it here. When looping over posts, we want our IDE to know each `$post` is an instance of `Post`. Let's take
 a look at the plain PHP implementation.
 
```php
# BlogModel

public function find(): array {
    // return ...
}
```

As of PHP 7.0, return types were added, and in PHP 7.1 they were refined with nullables and void. But there's no way 
 our IDE can know what's inside the array. So we're falling back to PHPDoc.
 
```php
/**
 * @return Post[]
 */
public function find(): array {
    // return ...
}
```

When using a "generic" implementation of e.g. a model class, type hinting the `-><hljs prop>find</hljs>()` method might not be possible. 
 So we're stuck with type hinting the `$posts` variable, in our code.
 
```php
/** @var Post[] $posts */
$posts = $blogModel-><hljs prop>find</hljs>();
```

Both the uncertainty of what's exactly in an array, the performance and maintenance impact because of scattered code,
 and the inconvenience when writing those extra checks, makes me long for a better solution. 

That solution, in my opinion is [generics](*https://wiki.php.net/rfc/generics). I won't explain in detail what generics 
 do, you can read the RFC to know that. But I will give you an example of how generics could solve these issues, guaranteeing 
 the developer would always have the correct data in a collection.
 
**Big note**: generics do not exist in PHP, yet. The RFC targeted PHP 7.1, and has no further information about the 
 future. The following code is based on the [the Iterator interface](*http://php.net/manual/en/class.iterator.php)
 and [the ArrayAccess interface](*http://php.net/manual/en/class.arrayaccess.php), which both exist as of PHP 5.0.
 At the end, we'll dive into a generics example, which is dummy code.
 
First we'll create a `Collection` class which works in PHP 5.0+. This class implements `Iterator` to be able to
 loop over its items, and `ArrayAccess` to be able to use array-like syntax to add and access items in the
 collection.
 
```php
class Collection implements Iterator, ArrayAccess
{
    private <hljs type>int</hljs> <hljs prop>$position</hljs>;

    private <hljs type>array</hljs> <hljs prop>$array</hljs> = [];

    public function __construct() {
        $this-><hljs prop>position</hljs> = 0;
    }

    public function current(): mixed {
        return $this-><hljs prop>array</hljs>[$this-><hljs prop>position</hljs>];
    }

    public function next(): void {
        ++$this-><hljs prop>position</hljs>;
    }

    public function key(): int {
        return $this-><hljs prop>position</hljs>;
    }

    public function valid(): bool {
        return <hljs prop>array_key_exists</hljs>($this-><hljs prop>position</hljs>, $this-><hljs prop>array</hljs>);
    }

    public function rewind(): void {
        $this-><hljs prop>position</hljs> = 0;
    }

    public function offsetExists($offset): bool {
        return <hljs prop>array_key_exists</hljs>($offset, $this-><hljs prop>array</hljs>);
    }

    public function offsetGet($offset): mixed {
        return $this-><hljs prop>array</hljs>[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void {
        if (<hljs prop>is_null</hljs>($offset)) {
            $this-><hljs prop>array</hljs>[] = $value;
        } else {
            $this-><hljs prop>array</hljs>[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void {
        unset($this-><hljs prop>array</hljs>[$offset]);
    }
}
```

Now we can use the class like this.

```php
$collection = new <hljs type>Collection</hljs>();
$collection[] = new <hljs type>Post</hljs>(1);

foreach ($collection as $item) {
    echo "{$item-><hljs prop>getId</hljs>()}\n";
}
```

Note that with this simple implementation, there's no guarantee that `$collection` only holds `<hljs type>Post</hljs>` object. For example, adding a string would work fine, but would break our loop.
 
```php
$collection[] = 'abc';

foreach ($collection as $item) {
    // This fails
    echo "{$item-><hljs prop>getId</hljs>()}\n";
}
```

With PHP as it is now, we could fix this problem by creating a `<hljs type>PostCollection</hljs>` class.

```php
class PostCollection extends Collection
{
    public function current() : <hljs type>?Post</hljs> {
        return parent::current();
    }

    public function offsetGet($offset) : <hljs type>?Post</hljs> {
        return parent::offsetGet($offset);
    }

    public function offsetSet($offset, $value) {
        if (! $value instanceof <hljs type>Post</hljs>) {
            throw new <hljs type>InvalidArgumentException</hljs>("value must be instance of Post.");
        }

        parent::offsetSet($offset, $value);
    }
}
```

Now only `<hljs type>Post</hljs>` objects can be added to our collection.

```php
$collection = new <hljs type>PostCollection</hljs>();
$collection[] = new <hljs type>Post</hljs>(1);

<hljs striped>$collection[] = 'abc';</hljs>

foreach ($collection as $item) {
    echo "{$item-><hljs prop>getId</hljs>()}\n";
}
```

It works! Even without generics! There's only one issue, you might be able to guess it. This is not scalable. You need a
 separate implementation for every type of collection, even though the only difference between those classes would be the type. Also note that IDEs and static analysers will be able to correctly determine the type, based on the return type of `<hljs prop>offsetGet</hljs>` in `<hljs type>PostCollection</hljs>`.

You could probably make the subclasses even more convenient to create, by "abusing" 
 [late static binding](http://php.net/manual/en/language.oop5.late-static-bindings.php) and PHP's reflection API. But 
 you'd still need to create a class, for every type available.

## Glorious generics

With all that in mind, let's just take a look at the code we would be able to write if generics were implemented in PHP. 
 This would be **one class** which could be used for every type. For your convenience, I'll only be writing the changes 
 compared to the previous `Collection` class, so keep that in mind.
  
```php
class GenericCollection<<hljs generic>T</hljs>> implements Iterator, ArrayAccess
{
    public function current() : ?<hljs generic>T</hljs> {
        return $this->array[$this->position];
    }

    public function offsetGet($offset) : ?<hljs generic>T</hljs> {
        return $this->array[$offset] ?? null;
    }

    public function offsetSet($offset, $value) {
        if (! $value instanceof <hljs generic>T</hljs>) {
            throw new <hljs type>InvalidArgumentException</hljs>("value must be instance of {T}.");
        }

        if (<hljs prop>is_null</hljs>($offset)) {
            $this-><hljs prop>array</hljs>[] = $value;
        } else {
            $this-><hljs prop>array</hljs>[$offset] = $value;
        }
    }

    // public function __construct() ...
    // public function next() ...
    // public function key() ...
    // public function valid() ...
    // public function rewind() ...
    // public function offsetExists($offset) ...
}
```

```php
$collection = new <hljs type>GenericCollection</hljs><<hljs generic>Post</hljs>>();
$collection[] = new <hljs type>Post</hljs>(1);

// This would throw the InvalidArgumentException.
$collection[] = 'abc';

foreach ($collection as $item) {
    echo "{$item-><hljs prop>getId</hljs>()}\n";
}
```

And that's it! We're using `<hljs generic>T</hljs>` as a dynamic type, which can be checked before runtime. And again, the `<hljs type>GenericCollection</hljs>` 
 class would be usable for every type, always.

---



In today's blog post we'll explore some common problems with arrays in PHP. All the problems and issues listed could be solved with a pending RFC which adds generics to PHP. We won't explore in too much detail what generics are. But at the end of this read you should have a good idea as to why they are useful, and why we really want them in PHP. So without further ado, lets dive into the subject.
 
---

Imagine you have a collection of blog posts, loaded from a data source.

```php
$posts = $blogModel->find();
```

Now you want to loop over every post, and do *something* with its data; lets say, the `id`.

```php
foreach ($posts as $post) {
    $id = $post->getId();
    
    // Do something
}
```

This is a scenario that happens often. And its this scenario we'll explore to discuss why generics are awesome, and why
 the PHP community desperately needs them.
 
Lets take a look at the problems of the above approach.

### Data integrity

In PHP, an array is a collection of.. things.

```php
$posts = [
    'foo',
    null,
    self::BAR,
    new Post('Lorem'),
];
```

Looping over this set of posts would result in a fatal error.

```
PHP Fatal error:  Uncaught Error: Call to a member function getId() on string
```

We're calling `->getId()` on the string `'foo'`. Not done. When looping over an array, we want to be sure that 
 every value is of a certain type. We could do something like this.
 
```php
foreach ($posts as $post) {
    if (!$post instanceof Post) {
        continue;
    }

    $id = $post->getId();
    
    // Do something
}
```

This would work, but if you've written some production PHP code, you know these checks can grow quickly, and pollute 
 the codebase. In our example, we could verify the type of each entry in the `->find()` method on `$blogModel`.
 However, that's just moving the problem from one place to another. It's a bit better though.
 
There's another problem with data integrity. Say you have a method which requires an array of `Posts`.

```php
function handlePosts(array $posts) {
    foreach ($posts as $post) {
        // ...
    }
}
```

Again, we could add extra checks in this loop, but we could not guarantee that `$posts` only holds a collection of `Posts`.

[As of PHP 7.0](http://php.net/manual/en/functions.arguments.php#functions.variable-arg-list), you could use the `...` operator 
 to work around this issue.

```php
function handlePosts(Post ...$posts) {
    foreach ($posts as $post) {
        // ...
    }
}
```

But the downside of this approach: you would have to call the function with an unpacked array.

```php
handlePosts(...$posts);
```

### Performance

You can imagine it's better to know beforehand whether an array contains only elements of a certain type, rather then
 manually checking the types within a loop, every, single, time.
 
We can't do benchmarks on generics, because they don't exist yet, so its only guessing as to how they would impact performance.
 It's not insane to assume though, that PHP's optimised behaviour, written in C; is a better way to solve the problem than
 to write lots of userland code.
 
### Code completion

I don't know about you, but I use an IDE when writing PHP code. Code completion increases productivity immensely, so I'd also 
 like to use it here. When looping over posts, we want our IDE to know each `$post` is an instance of `Post`. Lets take
 a look at the plain PHP implementation.
 
```php
# BlogModel

public function find() : array {
    // return ...
}
```

As of PHP 7.0, return types were added, and in PHP 7.1 they were refined with nullables and void. But there's no way 
 our IDE can know what's in the array. So we're falling back to PHPDoc.
 
```php
/**
 * @return Post[]
 */
public function find() : array {
    // return ...
}
```

When using a "generic" implementation of eg. a model class, type hinting the `->find()` method might not be possible. 
 So we're stuck with type hinting the `$posts` variable, in our code.
 
```php
/** @var Blog[] $posts */
$posts = $blogModel->find();
```

Both the uncertainty of what's exactly in an array, the performance and maintenance impact because of scattered code,
 and the inconvenience when writing those extra checks, makes me long for a better solution. 

---

That solution, in my opinion is [generics](https://wiki.php.net/rfc/generics). I won't explain in detail what generics 
 do, you can read the RFC to know that. But I will give you an example of how generics could solve these issues, guaranteeing 
 the developer would always have the correct data in a collection.
 
Big **note**: generics do not exist in PHP, yet. The RFC targeted PHP 7.1, and has no further information about the 
 future. The following code is based on the [the Iterator interface](http://php.net/manual/en/class.iterator.php)
 and [the ArrayAccess interface](http://php.net/manual/en/class.arrayaccess.php), which both exist as of PHP 5.0.
 At the end, we'll dive into a generics example, which is dummy code.
 
First we'll create a `Collection` class which works in PHP 5.0+. This class implements `Iterator` to be able to
 loop over its items, and `ArrayAccess` to be able to use array-like syntax to add and access items in the
 collection.
 
```php
class Collection implements Iterator, ArrayAccess
{
    private $position;

    private $array = [];

    public function __construct() {
        $this->position = 0;
    }

    public function current() {
        return $this->array[$this->position];
    }

    public function next() {
        ++$this->position;
    }

    public function key() {
        return $this->position;
    }

    public function valid() {
        return isset($this->array[$this->position]);
    }

    public function rewind() {
        $this->position = 0;
    }

    public function offsetExists($offset) {
        return isset($this->array[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->array[$offset]) ? $this->array[$offset] : null;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->array[] = $value;
        } else {
            $this->array[$offset] = $value;
        }
    }

    public function offsetUnset($offset) {
        unset($this->array[$offset]);
    }
}
```

Now we can use the class like this.

```php
$collection = new Collection();
$collection[] = new Post(1);

foreach ($collection as $item) {
    echo "{$item->getId()}\n";
}
```

Note that again, there's no guarantee that `$collection` only holds `Posts`. Adding eg. a string would work fine, but 
 would break our loop.
 
```php
$collection[] = 'abc';

foreach ($collection as $item) {
    // This fails
    echo "{$item->getId()}\n";
}
```

With PHP as it is now, we could fix this problem by creating a `PostCollection` class. Note that I'm using nullable 
 return types, only available as of PHP 7.1.

```php
class PostCollection extends Collection
{
    public function current() : ?Post {
        return parent::current();
    }

    public function offsetGet($offset) : ?Post {
        return parent::offsetGet($offset);
    }

    public function offsetSet($offset, $value) {
        if (!$value instanceof Post) {
            throw new InvalidArgumentException("value must be instance of Post.");
        }

        parent::offsetSet($offset, $value);
    }
}
```

Now only `Posts` can be added to our collection.

```php
$collection = new PostCollection();
$collection[] = new Post(1);

// This would throw the InvalidArgumentException.
$collection[] = 'abc';

foreach ($collection as $item) {
    echo "{$item->getId()}\n";
}
```

It works! Even without generics! There's only one issue, you might be able to guess it. This is not scaleable. You need a
 separate implementation for every type of collection, even though the only difference between those classes would be the
 type.

You could probably make the subclasses even more convenient to create, by "abusing" 
 [late static binding](http://php.net/manual/en/language.oop5.late-static-bindings.php) and PHP's reflection API. But 
 you'd still need to create a class, for every type available.
  
### Glorious generics

With all that in mind, lets just take a look at the code we would be able to write if generics were implemented in PHP. 
 This would be **one class** which could be used for every type. For your convenience, I'll only be writing the changes 
 compared to the previous `Collection` class, so keep that in mind.
  
```php
class GenericCollection<T> implements Iterator, ArrayAccess
{
    public function current() : ?T {
        return $this->array[$this->position];
    }

    public function offsetGet($offset) : ?T {
        return isset($this->array[$offset]) ? $this->array[$offset] : null;
    }

    public function offsetSet($offset, $value) {
        if (!$value instanceof T) {
            throw new InvalidArgumentException("value must be instance of {T}.");
        }

        if (is_null($offset)) {
            $this->array[] = $value;
        } else {
            $this->array[$offset] = $value;
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
$collection = new GenericCollection<Post>();
$collection[] = new Post(1);

// This would throw the InvalidArgumentException.
$collection[] = 'abc';

foreach ($collection as $item) {
    echo "{$item->getId()}\n";
}
```

And that's it! We're using `<T>` as a dynamic type, which can be checked before runtime. And again, the `GenericCollection` 
 class would be usable for every type, always.
  
If you're as exited as me for generics (and this is only the tip of the iceberg by the way), you should spread the word 
 in the PHP community, and share the RFC: [https://wiki.php.net/rfc/generics](https://wiki.php.net/rfc/generics)

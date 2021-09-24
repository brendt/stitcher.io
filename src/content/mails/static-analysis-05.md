Ask anyone about what feature they'd absolutely want added in PHP, and I'd say there's a 50/50 chance the answer is "generics". It's however very unlikely they'll ever get added to PHP core: I asked Nikita about them, and [his answer](https://www.reddit.com/r/PHP/comments/j65968/ama_with_the_phpstorm_team_from_jetbrains_on/g7zg9mt/) made it clear that he doesn't see a way on how generics could work at runtime.

But let's back up for one second: maybe you don't know what generics are. So let me try and come up with as simple a definition possible:

> **Programming with generics** means that you write code without specifying types. Instead provide them at a later point, in order to make code more reusable.

A simple example is a generic "collection" class: say you want to model a collection of `<hljs type>Post</hljs>` objects, but also a collection of `<hljs type>Tag</hljs>` objects. You could use PHP's built-in arrays to do this, but then you're lacking type information about what's _in_ the array:

```php
$collectionOfPosts = [new <hljs type>Post</hljs>(), /* … */];

$collectionOfTags = [new <hljs type>Tag</hljs>(), /* … */];
```

You could add docblocks to add type information:

```php
/** @var <hljs type>Post[]</hljs> $collectionOfPosts */
$collectionOfPosts = [new <hljs type>Post</hljs>()];

/** @var <hljs type>Tag[]</hljs> $collectionOfTags */
$collectionOfTags = [new <hljs type>Tag</hljs>()];
```

Or you could create dedicated collection classes, each for a specific type:

```php
$collectionOfPosts = new <hljs type>PostCollection</hljs>(/* … */);

$collectionOfTags = new <hljs type>TagCollection</hljs>(/* … */);;
```

However, you can imagine how this solution doesn't really scale: what if you'd need tens of different collection classes? Even if you properly abstract all shared collection logic, you still end up with tons of classes.

Instead, let's imagine generics existed in PHP. With generics, there would only be one `<hljs type>Collection</hljs>` class:

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

There are some problems with the example above, but what's important is that `<hljs type>GenericType</hljs>` used on the class, as well as in both `<hljs prop>offsetGet</hljs>` and `<hljs prop>offsetSet</hljs>`. _If_ PHP were to support generics, we could now rewrite our initial example like so:

```php
$collectionOfPosts = new <hljs type>Collection</hljs><<hljs type>Post</hljs>>(/* … */);

$collectionOfTags = new <hljs type>Collection</hljs><<hljs type>Tag</hljs>>(/* … */);;
```

And our static analysers would know that `$collectionOfPosts` only contains `<hljs type>Post</hljs>` objects, while `$collectionOfTags` only contains `<hljs type>Tag</hljs>` objects.

Unfortunately, generics aren't supported… that is: not by PHP.

## Generics and Static Analysers

Luckily though, static analysers aren't limited to PHP's built-in type system: they can read docblocks as well. And within docblocks, we're free to do whatever we want! 

Let's rewrite our `<hljs type>Collection</hljs>` class with docblock generics. Most static analysers call them "template annotations", by the way:

```php
/** @template <hljs type>GenericType</hljs> */
class Collection
{
    // …
    
    /** @return <hljs type>GenericType</hljs> */
    public function offsetGet($offset) 
    { /* … */ }

    /** @param <hljs type>GenericType</hljs> $value */
    public function offsetSet($offset, $value): void
    { /* … */ }
}
```

It's not as pretty as the built-in syntax, but it works. With our generic implementation in place, you could pass the concrete types like so:

```php
/** @var <hljs type>Collection</hljs><<hljs type>Post</hljs>> $collectionOfPosts */
$collectionOfPosts = new <hljs type>Collection</hljs>(/* … */);

/** @var <hljs type>Collection</hljs><<hljs type>Tag</hljs>> $collectionOfTags */
$collectionOfTags = new <hljs type>Collection</hljs>(/* … */);
```

But now we're back at square one: adding docblocks on the variable assignments. Static analysers have one more trick up their sleeve though: type inference. Remember I told you that static analysers are pretty smart? They can often infer types based on context information; in this case that means: automatically detecting the generic type, without manually specifying it.

In order to support that, we'll need one more addition in the constructor of our collection class:

```php
/** @template <hljs type>GenericType</hljs> */
class Collection
{
    /** @param <hljs type>GenericType[]</hljs> $items */
    public function __construct(
        ...$items
    ) {
        $this-><hljs prop>items</hljs> = $items;
    }

    // …
}
```

Having specified that whatever input comes in the constructor, is the `<hljs type>GenericType</hljs>`, both Psalm and PHPStan are smart enough to automatically determine the type based on whatever is given to the constructor:

```php
$collectionOfPosts = new <hljs type>Collection</hljs>(
    new <hljs type>Post</hljs>, 
    new <hljs type>Post</hljs>,
    // …
);

$collectionOfTags = new <hljs type>Collection</hljs>(
    new <hljs type>Tag</hljs>, 
    new <hljs type>Tag</hljs>,
    // …
);
```

Now you can use those static insights:

```php
$collectionOfPosts[] = new <hljs type>Post</hljs>;

$collectionOfPosts[] = <hljs striped>new <hljs type>Tag</hljs></hljs>;

$collectionOfTags[0]->… 
// Static analysers know this is a Tag object
```

That's pretty neat! So what happens if you pass two different kind of objects into the same collection when constructing it? Well, static analysers are smart enough to detect that as well:

```php
$collectionOfPosts = new <hljs type>Collection</hljs>(
    new <hljs type>Post</hljs>, 
    <hljs striped>new <hljs type>Tag</hljs>,</hljs>
    // …
);
```

##### Error: Parameter #2 ...$items of class Collection constructor expects Post, Tag given.

There's one important note to make still. While it's awesome that tools like Psalm and PHPStan are smart enough to support generics, it's also important to have realtime feedback _while_ coding. PhpStorm recently [added basic support](https://blog.jetbrains.com/phpstorm/2021/07/phpstorm-2021-2-beta/) for generics, and I'm happy to say that most of the above examples, including generic type, inference works in PhpStorm! The only thing that PhpStorm doesn't report, is if you're passing mixed types into the constructor, a good reason to rely on external tools alongside our IDE.

I think adding realtime support in PhpStorm was the final missing piece of the puzzle for generics to actually become usable in our projects, because that realtime feedback is so valuable. Go ahead and play around with them, both [PHPStan](https://psalm.dev/docs/annotating_code/templated_annotations/) and [Psalm](https://psalm.dev/docs/annotating_code/templated_annotations/) have dedicated pages explaining generics in depth.

Are you getting excited about static analysis? There's more to come! See you tomorrow.

Brent

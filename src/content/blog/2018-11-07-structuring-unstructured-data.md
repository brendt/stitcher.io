## Have you ever…

…worked with an array in PHP that was actually more than just an array?
Did you use the array keys as fields? 
And did you feel the pain of not knowing exactly what was in that array? 
Not being sure whether the data in it is actually what you expect it to be, 
or what fields are available?

{{ ad:carbon }}

Let's visualise what I'm talking about:

```php
$line = // Get a line from a CSV file

import($line['id'], $line['name'], $line['amount']);
```

Another example: what about validated request data?

```php
function store(PostRequest $request, Post $post) 
{
    $data = $request->validated();
    
    $post->title = $data['title'];
    $post->author_id = $data['author_id'];
    
    // …
}
```

Arrays in PHP are a powerful and versatile data structure. 
At some point though, one should wonder whether there are better solutions for their problems.

## Know what you're writing

Regular readers of this blog may know that I've written about type theory in [the past](/blog/liskov-and-type-safety).
I won't revisit the pros and cons on strong type systems;
but I do want to say that arrays are a terrible choice 
if they are meant to be used as anything else but lists.

Here's a simple question for you: what's in this array?

```php
function doSomething(array $blogPost)
{
    $blogPost[/* Now what?? */];
}
```

In this case, there are several ways of knowing what data we're dealing with:

- Read the source code.
- Read the documentation.
- Dump `$blogPost` to inspect it. 
- Or use a debugger to inspect it.

I simply wanted to use this data, 
but next thing I know, I'm deep into debugging what kind of data I'm actually dealing with.
Are these really the things a programmer should be focused on?

Eliminating this uncertainty can reduce your cognitive load significantly.
This means you can focus on things that really matter: 
things like application and business logic.
You know, that's what most clients pay you to do.

It turns out that strongly typed systems can be a great help in understanding what exactly we're dealing with.
Languages like Rust, for example, solve this problem cleanly:

```c
struct BlogPost {
    title: String,
    body: String,
    active: bool,
}
``` 

A struct is what we need! 
Unfortunately PHP doesn't have structs.
It has arrays and objects, and that's it.

However, we _can_ do something like this:

```php
class BlogPost
{
    public string $title;
    public string $body;
    public bool $active;
}
```

Hang on, I know; we can't really do this, not _yet_.
PHP 7.4 [will add typed properties](*https://wiki.php.net/rfc/typed_properties_v2),
but they are still a long way away.

Imagine for a minute though that typed properties are already supported; 
we could use the previous example like so, which our IDE could auto complete:

```php
function doSomething(BlogPost $blogPost)
{
    $blogPost->title;
    $blogPost->body;
    $blogPost->active;
}
```

We could even support relations:

```php
class BlogPost
{
    public Author $author;
    
    // …
}
```

```php
function doSomething(BlogPost $blogPost)
{
    $blogPost->author->name;
}
```

Our IDE would always be able to tell us what data we're dealing with.
But of course, typed properties don't exist in PHP yet. 
What does exist… are docblocks.

```php
class BlogPost
{
    /** @var string */
    public $title;
    
    /** @var string */
    public $body;
    
    /** @var bool */
    public $active;
    
    /** @var \Author */
    public $author;
}
```

Docblocks are kind of a mess though: they are quite verbose and ugly;
but more importantly, they don't give any guarantees that the data is of the type they say it is! 

Luckily, PHP has its reflection API. With it, a lot more is possible, even today.
The above example can actually be type validated with a little reflection magic, 
as long as we don't write to the properties directly.

```php
$blogPost = new BlogPost([
    'title' => 'First',
    'body' => 'Lorem ipsum',
    'active' => false,
    'author' => new Author()
]);
```

That seems like a lot of overhead, right? 
Remember the first example though! 
We're not trying to construct these objects manually, 
we're reading them from a CSV file, a request or somewhere else:

```php
$blogPost = new BlogPost($line);
```

That's not bad, right?
And remember: a little reflection magic will ensure the values are of the correct type.
I'll show you how that works later.

I prefer this approach. 
It enables auto completion on what would otherwise be a black box.
While it requires a little more setup: you'll have to write definitions of data;
the benefits in the long run are worth it.

Sidenote: when I say "in the long run", I mean that this approach is especially useful in larger projects,
where you're working in the same code base with multiple developers, over a longer timespan.

## Reflecting types

So, how can we assure that our properties are of the correct type? 
Simple: read the `@var` docblock declartion, validate the value against that type, 
and only then set it. 
If the value is of a wrong type, we simply throw a `TypeError`.

Doing this extra check means we cannot write to the properties directly. 
At least not if they are declared public.
And in our case public properties is something we really want, 
because of when we're using these objects.
We want to be able to easily read data from them; 
we don't care as much on making writes easy, 
because we should never write to them after the object is constructed.

So we need a "hook" to validate a value against its type, before setting it.
There are two ways to do this in PHP.
Actually there are more, but these two are relevant.

### With a magic setter

A magic setter in combination with private or protected properties 
would allow us to run type validation before setting the value.

However, as mentioned before, we want a clean and public API to read from; 
so magic setters are, unfortunately, a no go.

### Via the constructor

Like in the previous example, we pass an array of data to the constructor,
and the constructor will map that data unto the properties of its class. 
This is the way to go.

Here's a simplified way of doing this:

```php
public function __construct(array $parameters)
{
    $publicProperties = $this->getPublicProperties();
   
    foreach ($publicProperties as $property) {
        $value = $parameters[$property->getName()];
        
        if (! $this->isValidType($property, $value) {
            throw new TypeError("…");
        }
        
        $this->{$property->getName()} = $value;
    }
}
```

Maybe you're curious as to what `isValidType` exactly does? 
Here is, again a simplified, implementation:

```php
protected function isValidType(ReflectionProperty $property, $value): bool
{
    $type = $this->getTypeDeclaration($property);
    
    return $value instanceof $type
        || gettype($value) === $type;
    }
}
```

Of course, there are some things missing here:

- Union types: `@var string|int`
- `@var mixed` support
- Generic collections: `@var \Foo[]`
- Nullable support: `@var int|null`

But it is very easy to add these checks to our `isValidType` method.
And that's exactly what we did by the way, we made this into a package: [spatie/data-transfer-object](*https://github.com/spatie/data-transfer-object).

## What about immutability?

How to handle immutability is the last question to answer.
If we use these objects to represent data from the outside,
are there any valid use cases for changing these objects once they are constructed?

In 98% of the cases, the answer should be plain and simple: no.
We'll never be able to change the data source, 
hence we shouldn't be able to change the object representing that source.

Real life projects are often not as black and white as I portray it here. 
While there might be some use cases, I think the mindset of "construct once, and never change"
is a good one.

So how to enforce this in PHP? 

Unfortunately: we don't.
There has been talk of so called "read only" properties in PHP's core,
but it's a difficult thing to get right.
Than what about our userland type system? 
Unless we're giving up the ease of reading, the auto completion part;
there will be no way to achieve this goal in PHP.

See, we _need_ magic getters to support this behaviour; 
at the same time we _don't_ want them.
They would negate one of the goals we're trying to achieve: easy discoverability. 

So for now, unfortunately, 
our package will allow writes to an object's properties after it is constructed.
We are just careful not to do it.



I hope this post inspired you to think about your own code bases, 
and that you might be prompted to try this pattern out in your projects;
with [our package](*https://github.com/spatie/data-transfer-object) or your own implementation.

If there are any thoughts coming to your mind, 
if you want to discuss this further; I'd love to here from you!
You can reach me via [Twitter](*https://twitter.com/brendt_gd) or [e-mail](mailto:brendt@stitcher.io). 

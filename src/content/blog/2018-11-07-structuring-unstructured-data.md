## Have you ever…

…worked with an array in PHP that was actually more than just an array?
Did you use the array keys as fields? 
And did you feel the pain of not knowing exactly what was in that array? 
Not being sure whether the data in it is actually what you expect it to be, 
or what fields are available?

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

Regular readers of this blog may know that I've written about type theory in [the past](*/blog/liskov-and-type-safety).
I don't want to debate today whether strong type systems are beneficial or not;
but I do want to say that arrays in PHP are a terrible choice if they are meant to be used as anything else but lists.

Here's a simple question for you: what's in this array?

```php
function doSomething(array $blogPost)
{
    $blogPost[/* Now what?? */];
}
```

There are several ways of knowing what data you're dealing with:

- Read the source code.
- Read the documentation.
- Dump `$blogPost` to inspect it. 
- Or use a debugger to inspect it.

I just wanted to do a simple thing with this data, 
but next I know I'm deep into debugging what data I'm actually dealing with.
Are these really the things a programmer should be focused on?

Knowing what data you're dealing with can reduce your cognitive load significantly.
This means you can focus on things that really matter. 
Things like application- and business logic.
You know, that's what most clients pay you to do.

It turns out that strongly typed systems can be a great help in understanding what data we're actually dealing with.
Languages like Rust, for example, solve this problem cleanly:

```c
struct BlogPost {
    title: String,
    body: String,
    active: bool,
}
``` 

A struct, that's what we need! 
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

Imagine if we'd already have typed properties support, 
we could write our previous example like so:

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

Our IDE would always be able to tell us what data we're exactly dealing with.
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
but more important: they don't actually give any guarantees that the data is of the type they say it is! 

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
We're not trying to construct these object manually, we're reading them from a CSV file, a request or something else:

```php
$blogPost = new BlogPost($line);
```

That's not bad, right?
And remember: a little reflection magic will ensure the values are of the correct type;
I'll show you how exactly later.

I prefer this approach. 
It enables auto completion on what would otherwise be a black box.
It requires a little more setup work: you'll have to make definitions for certain types,
but the benefit in the long run is worth it.

Sidenote: when I say "in the long run", I mean that this approach is especially useful in larger projects,
where you're working in the same code base with multiple developers, over a longer timespan.

{{ ad }}

## Reflecting types

## What about immutability?

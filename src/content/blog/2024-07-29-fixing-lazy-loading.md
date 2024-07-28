I'm writing an ORM. I know, it'll probably drive me insane. Why am I doing it? Mainly because of the interesting challenge. Also, because I've been building a [framework](https://github.com/tempestphp/tempest-framework) for the past two years now. I want to share a small part of the design process today, because it's these kinds of details that I like most about programming: figuring out what the public API will look like, facing challenges, and fixing them.

Whether you plan on using Tempest or not, whether you plan on writing your own framework one day or not, I hope you'll find what comes next interesting and inspiring!

## Setting the scene

First, let's talk about some core characteristics of this ORM. It's important to understand the challenges I ran into down the line. For one, I find static analysis important. That's why I want typed properties to be the core of Tempest's design. 

A model class looks something like this:

```php
class Book
{
    public function __construct(
        public string $title,
        public DateTimeImmutable|null $publishedAt,
        public Author $author,
        /** @var Chapter[] $chapters */
        public array $chapters,
    ) {}
}
```

Note how we're not doing any manual relationship configuration. In fact, it's one of the key characteristics of Tempest to be smart enough to reduce as much config as possible. The `{php}Author` type of the `{php}$author` property is enough to determine that this is a `belongs to` relation, the `{php}/** @var Chapter[] $chapters */` docblock on `$chapters` is enough to know this is a `has many` relation. Yes, I know docblocks suck. Let's hope PHP gets typed arrays one day. Why a docblock and not an attribute? Well we need the docblock any way to get proper type analysis on the property, so it would only make our lives more difficult if we had to add an attribute _on top of_ a docblock:

```php
/** @var Chapter[] $chapters */ // Meh
#[HasMany(Chapter::class)] // Even more meh
public array $chapters,
```

Another important design decision in Tempest is that model classes aren't directly tied to the database. You _can_ load model classes from a database if you want to, but they could also be persisted to a JSON file, a Redis store, XML, whatever you can think of. Granted: your average app will persist most of its models to a database, so there is a trait in Tempest to help you with that:

```php
class Book implements Model
{
    use IsModel;
    
    public function __construct(
        public string $title,
        public DateTimeImmutable|null $publishedAt,
        public Author $author,
        /** @var Chapter[] $chapters */
        public array $chapters,
    ) {}
}
```

(Note to self: I still have to rename it to `{php}DatabaseModel`.)

Anyway, those are models. Let's talk about lazy loading, now.

## Lazy loading

Let's say you retrieve a book from the database:

```php
$book = Book::find($id);
```

By default, it won't load relations like `$author` and `$chapters`. Think about it: in this example it would not be a biggy to load two relations, but what happens if each `$chapter` has a backreference to the book? And each `$author` has an array of its books as well? Or, what if an author has a relation to a `$publisher`, which has a list of all books published, … will you always load _all_ relations? No, that wouldn't be very performant. That's why, by default, Tempest doesn't load relations. So, if you need a relation to be loaded, you'll have to specifically say so:

```php
$book = Book::query()->with('author')->find($id);
```

This is no different from what Laravel does by the way, it also doesn't load relations up front. However, Tempest goes one step further. If you happen access a relation that's not loaded, Tempest won't load it for you by default. Instead, it will throw an error:

```php
$book = Book::find($id);

$book->{:hl-striped:author:}; // MissingRelation
```

This is different from Laravel, and a very deliberate decision. In Laravel, Eloquent will lazily load missing relations for you by default. So if the relation isn't currently present, Laravel will perform an additional query on the fly to retrieve it for you. In an isolated example this again doesn't seem like a big deal, but let's say we're looping over an array of books instead:

```html
<div :foreach="$this->books as $book">
    {{ $book->title }} by {{ $book->author->name }}
</div>
```

If we didn't load `$author` up front, lazy loading would perform a _separate_ query for _every_ book in the array. This is the infamous n+1 problem, and it's one of the most common performance issues in Laravel. I can't remember how many hours I spent chasing and fixing n+1 issues in Laravel applications. It often had seconds of impact on loading time in production apps, and often required hours of debugging and fixing. Remember that in real life, model schemes are much more complex that one simple `belongs to` relation, it really was a time sink.

Now, Laravel has addressed this issue a year or two ago: you can now [disable lazy loading manually](https://laravel-news.com/disable-eloquent-lazy-loading-during-development). By default, Laravel still lazily loads everything, but good for them acknowledging the issue and providing an easy way of disabling it.

I'm not here to dis Laravel by the way, I love Laravel, and I totally understand the decision to make lazy loading the default. Starting from scratch though, I have a opportunity to rethink the pain points I experienced with Laravel, and so I decided on another approach: lazy loading is off by default, but you can turn it on manually for specific properties. It looks like this:

```php
#[Lazy] public Author $author,
```

Personally, I like these defaults better: nothing is lazily loaded, unless you specifically say so. It does require a programmer to micromanage which relations are loaded up front — true — but let me tell you, the same happens in Laravel as soon as you have an app whose model scheme is somewhat larger than two or three `belongs to` relations. I just did a quick search in the codebase of [RFC Vote](https://github.com/brendt/rfc-vote), which uses Laravel. It's not even a big app, and we're already manually loading relations in ten different places to prevent n+1 issues.

```php
return Rfc::query()
    ->with(['arguments', 'yesArguments', 'noArguments'])
    // …
    ->get();
```

So, that's the theory. How do you make this work in PHP? That's where things get fascinating.

## Just a tiny bit of hacking

Back to our initial example, the `{php}Book` model:

```php
class Book implements Model
{
    use IsModel;
    
    public function __construct(
        public string $title,
        public DateTimeImmutable|null $publishedAt,
        public Author $author,
        /** @var Chapter[] $chapters */
        public array $chapters,
    ) {}
}
```

Let's say we build and perform a query to select all relevant fields (everything besides the relations), and map that data into the object. This is what such an object would look like:

```php
Book {
    title: 'Timeline Taxi',
    publishedAt: null,
    author: {:hl-keyword:uninitialized:},
    chapters: {:hl-keyword:uninitialized:}, 
}
```

The interesting part here are the `unitialized` properties: typed properties that haven't been initialized yet. Now, because of how PHP works, uninitialized properties don't throw any errors until you try to access them. So it's perfectly fine to construct an object via reflection (skipping its constructor checks), and thus having the two relations be uninitialized instead of null or requiring a value.

This is an interesting mechanism we can use to have a "partially constructed object", while keeping the relation's type clean. If PHP didn't have an uninitialized property state, we'd have to resort to doing something like this to mark relations as optional:

```php
class Book implements Model
{
    use IsModel;
    
    public function __construct
        // …
        public Relation|Author $author,
        /** @var Chapter[] $chapters */
        public Relation|array $chapters,
    ) {}
}
```

Now I don't know about you, but I like to write as little code as possible, especially if the framework can do the work for me.

So, what's next? We need a way to execute some checks when a user tries to access this uninitialized property. Easy enough, right? PHP has a magic method `{:hl-property:__get:}()` that will trigger every time you access a non-existing property on an object. That should be easy, right?

```php
trait IsModel
{
    public function __get(string $name): mixed
    {
        $property = new ReflectionProperty($this, $name);

        // If this property is allowed to lazy load, load it 
        if (attribute(Lazy::class)->in($property)->exists()) {
            $this->load($name);

            return $property->getValue($this);
        }

        // Otherwise, throw an exception
        throw new MissingRelation($this, $name);
    }
}
```

Well… nope. That doesn't work. Wanna know why? Trying to access an uninitialized property on a class _doesn't trigger_ `{:hl-property:__get:}()`! Why? You wonder? Because the property we're trying to access does exist, it simply isn't initialized yet. So instead of triggering get, PHP will throw an error:

```
Fatal error: Uncaught Error: Typed property Book::$author 
must not be accessed before initialization
```

You didn't think it was gonna be so easy, did you? This is PHP, after all 😂

Now, luckily, there is a way around this! This is PHP, after all. 😎

For the workaround to work though, I'll need to explain how model objects are created. I already mentioned they are constructed without triggering the constructor. That's done via reflection. Let's assume we have this array of items — the result of our initial `{sql}SELECT` query:

```php
[
    'title' => 'Timeline Taxi',
    'publishedAt' => null,
]
```

Tempest has a class `{php}ArrayToObjectMapper`, which knows how to transform this data into an object (it can also do this with nested properties, etc, but let's keep it simple for this example). From the outside, it looks something like this:

```php
$book = map($query)->to(Book::class);
```

Now, this is what happens behind the scenes: the mapper will create an object of `{Book}`, without its constructor, it will then loop over all public properties, and it will set those property values to what's present in the input array (the `$query` data):

```php
final readonly class ArrayToObjectMapper implements Mapper
{

    public function map(mixed $from, mixed $to): object
    {
        $reflection = new ReflectionClass($to);
        
        $object = $reflection->newInstanceWithoutConstructor();
        
        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if (! array_key_exists($property->getName(), $from)) {
                continue;
            }
            
            $property->setValue($object, $from[$property->getName()]);
        }
    }
}
```

Now, this is a simplification of [what's really happening](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Mapper/Mappers/ArrayToObjectMapper.php), but it's enough to understand how we're going to hack PHP &amp;lt;insert delirious laugh here&amp;gt;!

I might be overhyping it a bit though, since it's just one line of code. Remember that we want `{:hl-property:__get:}()` to trigger on properties that aren't initialized. In other words, we want `{:hl-property:__get:}()` to trigger on properties that didn't have a value present in the `$from` array.

Turns out you can do exactly that, even for properties that aren't initialized, by manually unsetting the property, like so:

```php
public function map(mixed $from, mixed $to): object
{
    $reflection = new ReflectionClass($to);
    
    $object = $reflection->newInstanceWithoutConstructor();
    
    foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
        if (! array_key_exists($property->getName(), $from)) {
            {+unset($object->{$property->getName()});+}
            
            continue;
        }
        
        $property->setValue($object, $from[$property->getName()]);
    }
}
```

Indeed, by _unsetting_ a property, even though it was never initialized, and even though it exists in the class definition, will make PHP think it doesn't exist, and trigger `{:hl-property:__get:}()`! 🎉

Beautiful, isn't it? Of course, it would be much easier if there was a magic method that hooked into uninitialized property access, but that, we don't have. Nevertheless, the end result is pretty neat: we can retrieve models from a database, its relations aren't loaded by default, Tempest will throw a proper error if you try to access them, and properties marked with `{php}#[Lazy]` will lazily load values for you, but you'll need to be explicit about it.

Well, that's about all I wanted to share today. Next up my list is supporting eager loads — basically _always_ loading a relation, even if you didn't specify it manually.  

Leave your thoughts in the comments down below! Oh and, if you're wondering about that book title I used in the examples, you can read about it here: [https://stitcher.io/blog/timeline-taxi-chapter-01](https://stitcher.io/blog/timeline-taxi-chapter-01).
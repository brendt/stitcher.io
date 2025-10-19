_This post was originally published on the [Tempest blog](https://tempestphp.com/blog/request-objects-in-tempest/)._

Tempest's tagline is "the framework that gets out of your way". One of the best examples of that principle in action is request validation. A pattern I learned to appreciate over the years was to represent "raw data" (like for example, request data), as typed objects in PHP — so-called "data transfer objects". The sooner I have a typed object within my app's lifecycle, the sooner I have a bunch of guarantees about that data, which makes coding a lot easier.

For example: not having to worry about whether the "title of the book" is actually present in the request's body. If we have an object of `BookData`, and that object has a typed property `public string $title` then we don't have to worry about adding extra `isset` or `null` checks, and fallbacks all over the place.

Data transfer objects aren't unheard of in frameworks like [Symfony](https://symfony.com/blog/new-in-symfony-6-3-mapping-request-data-to-typed-objects) or [Laravel](https://spatie.be/docs/laravel-data/v4/introduction), although Tempest takes it a couple of steps further. In Tempest, the starting point of "the request validation flow" is _that_ data object, because _that object_ is what we're _actually_ interested in.

Here's what such a data object looks like:

```php
final class BookData
{
    public string $title;

    public string $description;

    public ?DateTimeImmutable $publishedAt = null;
}
```

It doesn't get much simpler than this, right? We have an object representing the fields we expect from the request. Now how do we get the request data into that object? There are several ways of doing so. I'll start by showing the most verbose way, mostly to understand what's going on. This approach makes use of the `map()` function. Tempest has a built-in [mapper component](/docs/framework/mapper), which is responsible to map data from one point to another. It could from an array to an object, object to json, one class to another, … Or, in our case: the request to our data object.

Here's what that looks like in practice:

```php
use Tempest\Router\Request;
use function Tempest\map;

final readonly class BookController
{
    #[Post('/books')]
    public function store(Request $request): Redirect
    {
        $bookData = map($request)->to(BookData::class);
        
        // Do something with that book data
    }
}
```

We have a controller action to store a book, we _inject_ the `Request` class into that action (this class can be injected everywhere when we're running a web app). Next, we map the request to our `BookData` class, and… that's it! We have a validated book object:

```php
/*
 * Book {
 *      title: Timeline Taxi
 *      description: Brent's newest sci-fi novel
 *      publishedAt: 2024-10-01 00:00:00
 * }
 */
```

Now, hang on — _validated_? Yes, that's what I mean when I say that "Tempest gets out of your way": `BookData` uses typed properties, which means we can infer a lot of validation rules from those type signatures alone: `title` and `description` are required since these aren't nullable properties, they should both be text; `publishedAt` is optional, and it expects a valid date time string to be passed via the request.

Tempest infers all this information just by looking at the object itself, without you having to hand-hold the framework every step of the way. There are of course validation attributes for rules that can't be inferred by the type definition itself, but you already get a lot out of the box just by using types.

```php
use Tempest\Validation\Rules\DateTimeFormat;
use Tempest\Validation\Rules\Length;

final class BookData
{
    #[Length(min: 5, max: 50)]
    public string $title;

    public string $description;

    #[DateTimeFormat('Y-m-d')]
    public ?DateTimeImmutable $publishedAt = null;
}
```

This kind of validation also works with nested objects, by the way. Here's for example an `Author` class:

```php
use Tempest\Validation\Rules\Length;
use Tempest\Validation\Rules\Email;

final class Author
{
    #[Length(min: 2)]
    public string $name;

    #[Email]
    public string $email;
}
```

Which can be used on the `Book` class:

```php
final class Book
{
    #[Length(min: 2)]
    public string $title;

    public string $description;

    public ?DateTimeImmutable $publishedAt = null;

    public Author $author;
}
```

Now any request mapped to `Book` will expect the `author.name` and `author.email` fields to be present as well.


## Request Objects

With validation out of the way, let's take a look at other approaches of mapping request data to objects. Since request objects are such a common use case, Tempest allows you to make custom request implementations. There's only a very small difference between a standalone data object and a request object though: a request object implements the `Request` interface. Tempest also provides a `IsRequest` trait that will take care of all the interface-related code. This interface/trait combination is a pattern you'll see all throughout Tempest, it's a very deliberate choice instead of relying on abstract classes, but that's a topic for another day.

Here's what our `BookRequest` looks like:

```php
use Tempest\Router\IsRequest;
use Tempest\Router\Request;

final class BookRequest implements Request
{
    use IsRequest;

    #[Length(min: 5, max: 50)]
    public string $title;

    public string $description;
    
    // …
}
```

With this request class, we can now simply inject it, and we're done. No more mapping from the request to the data object. Of course, Tempest has taken care of validation as well: by the time you've reached the controller, you're certain that whatever data is present, is also valid.

```php
use function Tempest\map;

final readonly class BookController
{
    #[Post('/books')]
    public function store(BookRequest $request): Redirect
    {
        // Do something with the request
    }
}
```

## Mapping to models

You might be thinking: a request can be mapped to virtually any kind of object. What about models then? Indeed. Requests can be mapped to models directly as well! Let's do some quick setup work.

First we add `database.config.php`, Tempest will discover it, so you can place it anywhere you like. In this example we'll use sqlite as our database:

```php
// app/database.config.php

use Tempest\Database\Config\SQLiteConfig;

return new SQLiteConfig(
    path: __DIR__ . '/database.sqlite'
);
```

Next, create a migration. For the sake of simplicity I like to use raw SQL migrations. You can read more about them [here](https://tempestphp.com/main/essentials/database#migrations). These are discovered as well, so you can place them wherever suits you:

```sql
-- app/Migrations/CreateBookTable.sql

CREATE TABLE `Books`
(
    `id` INTEGER PRIMARY KEY,
    `title` TEXT NOT NULL,
    `description` TEXT NOT NULL,
    `publishedAt` DATETIME
)
```

Next, we'll create a `Book` class, which implements `DatabaseModel` and uses the `IsDatabaseModel` trait:

```php
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\DatabaseModel;

final class Book implements DatabaseModel
{
    use IsDatabaseModel;

    public string $title;

    public string $description;

    public ?DateTimeImmutable $publishedAt = null;
}
```

Then we run our migrations:

```console
~ tempest migrate:up

<em>Migrate up…</em>
- 0000-00-00_create_migrations_table
- CreateBookTable_0

<success>Migrated 2 migrations</success>
```

And, finally, we create our controller class, this time mapping the request straight to the `Book`:

```php
use function Tempest\map;

final readonly class BookController
{
    #[Post('/books')]
    public function store(Request $request): Redirect
    {
        $book = map($request)->to(Book::class);
        
        $book->save();
        
        // …
    }
}
```

And that is all! Pretty clean, right?  
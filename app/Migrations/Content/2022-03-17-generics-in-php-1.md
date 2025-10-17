Generics in PHP. I know I’d want them. And I know a lot of developers who agree. On the other hand, there is a group of PHP programmers, maybe even larger, that say they don’t know what generics are, or why they should care.

I’m going to do a series on this blog about generics and PHP. We’ll start from the beginning, but quickly work our way to the more complex topics. We’ll talk about what generics are, why PHP doesn’t support them, and what’s possible in the future.

Let’s get started.

<div class="sidenote">
<div class="center">
    <a href="https://www.youtube.com/watch?v=c8hQ1fWU_mQ&list=PL0bgkxUS9EaKyOugEDffRzsvupBE2YEoD&index=1&ab_channel=BrentRoose" target="_blank" rel="noopener noreferrer">
        <img class="small" src="/resources/img/static/generics-thumb-1.png">
        <p><em class="center small">You can watch the video instead of reading a blog post — if you prefer that!</em></p>
    </a>
</div>
</div>

Every programming language has some kind of type system. Some languages have a very strict implementation, while others — PHP falls in this category — are much more lenient.

Now, type systems are used for a variety of reasons; the most obvious one is **type validation**.

Let’s imagine we have a function that takes two numbers, two integers; and does some kind of maths operation on them:

```php
function add($a, $b) 
{
    return $a + $b;
}
```

PHP will happily allow you to pass any kind of data to that function, numbers, strings, booleans, doesn’t matter. PHP will try its best to convert a variable whenever it makes sense, like for example adding them together. 

```php
<hljs prop>add</hljs>('1', '2');
```

But those conversions — type juggling — often lead to unexpected results, if not to say: bugs and crashes.


```php
<hljs prop>add</hljs>([], true); // ?
```

Now, we could manually write code to check whether our maths addition will work with any given input:

```php
function add($a, $b) 
{
    if (!<hljs prop>is_int</hljs>($a) || !<hljs prop>is_int</hljs>($b)) {
        return null;
    }
    
    return $a + $b;
}
```

Or we could make use of PHPs built-in type hints — built-in shorthands for what we’d otherwise do manually:

```php
function add(<hljs type>int</hljs> $a, <hljs type>int</hljs> $b): int 
{
    return $a + $b;
}
```

Many developers in the PHP community say they don’t really care about these type hints because they know they should only pass integers to this function — they wrote it, after all.

However, that kind of reasoning quickly falls apart: you’re often not the only one working in that codebase, you’re also using code that you haven’t written yourself — think about how many packages you’re pulling in with composer. And so, while this example in isolation might not seem to be that big a deal, type checking does come in handy once your code starts to grow.

Besides that, adding type hints not only guards against invalid state, but they also **clarify** what kind of input is expected from us, programmers. Types often make it so that you don’t need to read external documentation, because much of what a function does is already encapsulated by its type definition.

IDEs make heavy use of this principle: they can tell the programmer what kind of input is expected by a function or what fields and methods are available on an object — because it belongs to a class. IDEs make our code writing so much more productive, in large part because they can statically analyse type hints across our codebase.

Keep that word in mind: **static analysis** — it’s going to be very important later in this series. It means that programs, IDEs or other kinds of “static analysers” can look at our code, and without running it tell us whether it will work or not — at least, to some degree. If we’re passing a string to our function that takes an integer, our IDE will tell us we’re doing something wrong — something that would lead to a crashing program at runtime; but our IDE is able to tell us without having to actually run the code.

On the other hand, type systems have their limitations. A common example is a “list of items”:

```php
class Collection extends ArrayObject
{
    public function offsetGet(<hljs type>mixed</hljs> $key): mixed 
    { /* … */ }
    
    public function filter(<hljs type>Closure</hljs> $fn): self 
    { /* … */ }
    
    public function map(<hljs type>Closure</hljs> $fn): self 
    { /* … */ }
}
```

A collection has a bunch of methods that work with any kind of input: looping, filtering, mapping, you name it; a collection implementation shouldn’t care about whether it’s dealing with strings or integers.

But let’s look at it from an outsider’s perspective. What happens if we want to be sure that one collection only contains strings, and another one only contains `<hljs type>User</hljs>` objects. The collection itself doesn’t care when looping over its items, but we do. We want to know whether this item in a loop is a User or a string — that’s quite the difference. But without proper type information, our IDE is operating in the dark.

```php
$users = new <hljs type>Collection</hljs>();

// …

foreach ($users as $user) {
    $user-> // ?
}
```

Now, we could create separate implementations for each collection: one that only works with strings, and another that only works with `<hljs type>User</hljs>` objects: 

```php
class StringCollection extends Collection
{
    public function offsetGet(<hljs type>mixed</hljs> $key): string 
    { /* … */ }
}

class UserCollection extends Collection
{
    public function offsetGet(<hljs type>mixed</hljs> $key): User 
    { /* … */ }
}
```

But what if we need a third implementation? A fourth? Maybe ten or twenty. It becomes quite painful to manage all that code.

That’s where generics come in.

Now, to be clear: PHP doesn’t have generics. That’s a bold statement cutting quite a lot of corners, and we’re coming back to that later in this series. But for now it’s sufficient to say that what I’m showing next isn’t possible in PHP. But it is in many other languages.

Instead of creating a separate implementation for every possible type, many programming languages allow developers to define a “generic” type on the collection class:

```php
class Collection<<hljs generic>Type</hljs>> extends ArrayObject
{
    public function offsetGet(<hljs type>mixed</hljs> $key): <hljs generic>Type</hljs> 
    { /* … */ }
    
    // …
}
```

Basically we’re saying that the implementation of the collection class will work for any kind of input, but when we create an instance of a collection, we should specify a type. It’s a generic implementation, but it’s made specific depending on the programmer’s needs:

```php
$users = new <hljs type>Collection</hljs><<hljs generic>User</hljs>>();

$slugs = new <hljs type>Collection</hljs><<hljs generic>string</hljs>>();
```

It might seem like a small thing to do: adding a type. But that type alone opens a world of possibilities. Our IDE now knows what kind of data is in a collection, it can tell us whether we’re adding an item with the wrong type; it can tell us what we can do with items when iterating over a collection, it can tell us whether we’re passing the collection to a function that knows how to work with those specific items.

And while we could technically achieve the same by manually implementing a collection for every type we need; a generic implementation would be a significant improvement for you and me, developers who are writing and maintaining code.

So, why don’t we have generics in PHP? What other things can we do with them besides a boring collection? Can we add support for them? We’re going to answer all those questions in this mini series. And to be clear up front: my goal with this series is to teach you about generics, but equally important is that I want to create awareness about how we’re missing out with PHP. I want that to change.

{{ cta:mail }}

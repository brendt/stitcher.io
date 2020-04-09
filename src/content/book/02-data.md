At the core of every project, you find data. Almost every application's task can be summarized like so:  provide, interpret and manipulate data in whatever way the business wants.

You probably noticed this yourself too: at the start of a project you don't start building controllers and jobs, you start by building, what Laravel calls, models. Large projects benefit from making ERDs or other kinds of diagrams to conceptualise what data will be handled by the application. Only when that's clear, you can start building the entry points and hooks that work with this data.

In this chapter we'll take a close look at how to work with data in a structured way, so that all developers in your team can write applications to handle this data in predictable and safe ways.

You might be thinking of models right now, but we need to take a few more steps back at first. 

## Type theory

In order to understand the use of data transfer objects — spoiler: those are what this chapter is about — you'll need to have some background knowledge about type systems.

Not everyone agrees on the vocabulary used when talking about type systems. So let's clarify a few terms in the way that I will use them here.

The strength of a type system — strong or weak — defines whether a variable can change its type after it's defined. 

A simple example: given a string variable `$a = 'test';`; a weak type system allows you to re-assign that variable to another type, for example `$a = 1;`, an integer.

PHP is a weakly typed language, so let's look at a more real-life example:

```php
$id = '1'; // Eg. an id retrieved from the URL

function find(<hljs type>int</hljs> $id): Model
{
    // The input '1' will automatically be cast to an int
}

<hljs prop>find</hljs>($id);
```

To be clear: it makes sense for PHP to have a weak type system. It's a language that mainly works with HTTP requests, and everything is basically a string.

You might think that in modern PHP, you can avoid this behind-the-scenes type switching — type juggling — by using the strict types feature, but that's not completely true. 
Declaring strict types prevents other types being passed into a function, 
but you can still change the value of the variable in the function itself.

```php
declare(<hljs prop>strict_types</hljs>=1);

function find(<hljs type>int</hljs> $id): Model
{
    $id = '' . $id;

    /*
     * This is perfectly allowed in PHP
     * `$id` is a string now.
     */

    // …
}

<hljs prop>find</hljs>('1'); // This would trigger a TypeError.

<hljs prop>find</hljs>(1); // This would be fine.
```

Even with strict types and type hints, PHP's type system is weak. 
Type hints only ensure a variable's type at that point in time, 
without a guarantee about any future value that variable might have.

Like I said before: it makes sense for PHP to have a weak type system, since all input it has to deal with starts out as a string.
There is an interesting property to strong types though: they come with a few guarantees.
If a variable has a type that's unchangeable, a whole range of unexpected behaviour simply cannot happen anymore.

You see, it's mathematically provable that if a strongly typed program compiles,
it's impossible for that program to have a range of bugs which would be able to exist in weakly typed languages.
In other words, strong types give the programmer a better insurance that the code actually behaves how it's supposed to.

As a sidenote: this doesn't mean that a strongly typed language cannot have bugs! 
You're perfectly able to write a buggy implementation.
But when a strongly typed program compiles successfully, 
you're sure a certain set of bugs and errors can't occur in that program.

> Strong type systems allow developers to have much more insight into the program when writing the code, instead of having to run it.

There's one more concept we need to look at: static and dynamic types – and this is where things start to get interesting. 

As you're probably aware, PHP is an interpreted language.
This means that a PHP script is translated to machine code at runtime. 
When you send a request to a server running PHP, 
it will take those plain `.php` files, and parse the text in it to something the processor can execute.
  
Again, this is one of PHP's strengths: the simplicity of writing a script, refreshing the page, and everything is there.
That's a noticeable difference compared to languages that have to be compiled before they can be run. 

Obviously there are caching mechanisms which optimise this, so the above statement is an oversimplification. It's good enough to get the next point though.

Once again, there's a downside: since PHP only checks its types at runtime, the program's type checks can fail when running. So even when you're using type hints, your program stilled crashed at runtime.

This type checking at runtime makes PHP a dynamically typed language.
A statically typed language on the other hand
will have all its type checks done before the code is executed. 

As of PHP 7.0, its type system has been improved quite a lot. So much so that tools like [PHPStan](*https://github.com/phpstan/phpstan), [phan](*https://github.com/phan/phan) and [psalm](*https://github.com/vimeo/psalm) started to become very popular lately. These tools take the dynamic language that is PHP, but run a bunch of statical analyses on your code.

These opt-in libraries can offer quite a lot of insight into your code, without ever having to run or unit test it, an IDE like PhpStorm also has many of these static checks built-in.

With all this background information in mind, it's time to return to the core of our application: data.

## Structuring unstructured data 

Have you ever had to work with an "array of stuff" that was actually more than just a list? 
Did you use the array keys as fields? 
And did you feel the pain of not knowing exactly what was in that array? 
Not being sure whether the data in it is actually what you expect it to be, 
or what fields are available?

Let's visualise what I'm talking about: working with Laravel's requests. Think of this example as a basic CRUD operation to update an existing customer:

```php
function store(<hljs type>CustomerRequest</hljs> $request, <hljs type>Customer</hljs> $customer) 
{
    $validated = $request-><hljs prop>validated</hljs>();
    
    $customer->name = $validated['name'];
    $customer->email = $validated['email'];
    
    // …
}
```

You might already see the problem arising: we don't know exactly what data is available in the `$validated` array. While arrays in PHP are a versatile and powerful data structure, when they are used to represent something other than "a list of things", there probably are better ways to solve the problem.

Before looking at solutions, here's what you _could_ do when your in such a situation, and want to know about the contents of `$validated`: 

- Read the source code
- Read the documentation
- Dump `$validated` to inspect it 
- Or use a debugger to inspect it

Now imagine for a minute that you're working with a team of several developers on this project, and that your colleague has written this piece of code five months ago: I fairly confident that you will not know what data you're working with, without doing any of the cumbersome things listed above.

It turns out that strongly typed systems in combination with static analysis can be a great help in understanding what exactly we're dealing with. Languages like Rust, for example, solve this problem cleanly:

```
<hljs keyword>struct</hljs> <hljs type>CustomerData</hljs> {
    <hljs prop>name</hljs>: <hljs type>String</hljs>,
    <hljs prop>email</hljs>: <hljs type>String</hljs>,
    <hljs prop>birth_date</hljs>: <hljs type>Date</hljs>,
}
``` 

A struct is what we need! 
Unfortunately PHP doesn't have structs.
It has arrays and objects, and that's it.

However… objects and classes might be enough:

```php
class CustomerData
{
    public <hljs type>string</hljs> $name;
    public <hljs type>string</hljs> $email;
    public <hljs type>Carbon</hljs> $birth_date;
}
```

Now I know; typed properties are only available as of PHP 7.4. Depending on when you read this book, you might not be able to use them yet — I have a solution for you later in this chapter, keep on reading.

Those of us who are able to use typed properties can do stuff like this:

```php
function store(<hljs type>CustomerRequest</hljs> $request, <hljs type>Customer</hljs> $customer) 
{
    $validated = <hljs type>CustomerData</hljs>::<hljs prop>fromRequest</hljs>($request);
    
    $customer->name = $validated->name;
    $customer->email = $validated->email;
    $customer->birth_date = $validated->birth_date;
    
    // …
}
```

The static analyser built into your IDE would always be able to tell us what data we're dealing with.

This pattern of wrapping unstructured data in types, so that we can use our data in a reliable way, is called "data transfer objects". It's the first concrete pattern I highly recommend you to use in your larger-than-average Laravel projects. 

When discussing this book with your colleagues, friends or within the Laravel community, you might stumble upon people who don't share the same vision about strong type systems. There are in fact lots of people who prefer to embrace the dynamic and weak side of PHP. And there's definitely something to say for that. 

In my experience though there are more advantages to the strongly typed approach when working with a team of several developers on a project for serious amounts of time. You have to take every opportunity you can to reduce cognitive load. You don't want developers having to start debugging their code every time they want to know what exactly is in a variable. The information has to be right there at hand, so that developers can focus on what's important: building the application.

Of course, using DTOs comes with a price: there is not only the overhead of defining these classes; you also need to map, for example, request data onto a DTO. The benefits of using DTOs definitely outweigh this cost. Whatever time you lose by writing this code, you make up for in the long run. 

The question about constructing DTOs from "external" data is one that still needs answering though.

## DTO factories

How do we construct DTOs? I'll share two possibilities, and also explain which one has my personal preference.

The first one is the most correct one: using a dedicated factory.

```php
class CustomerDataFactory
{
    public function fromRequest(
       <hljs type>CustomerRequest</hljs> $request
    ): CustomerData {
        return new <hljs type>CustomerData</hljs>([
            'name' => $request-><hljs prop>get</hljs>('name'),
            'email' => $request-><hljs prop>get</hljs>('email'),
            'birth_date' => <hljs type>Carbon</hljs>::make(
                $request-><hljs prop>get</hljs>('birth_date')
            ),
        ]);
    }
}
```

This factory would live in the application layer. Making such dedicated classes keeps your code clean throughout the project.

While being the correct solution, you probably noticed I used a shorthand in a previous example, on the DTO class itself: `CustomerData::fromRequest`.

What's wrong with this approach? Well for one: it adds application-specific logic in the domain. The DTO which lives in the domain now has to know about the `CustomerRequest` class, which lives in the application layer.

```php
use <hljs type>Spatie\DataTransferObject\DataTransferObject</hljs>;

class CustomerData extends DataTransferObject
{
    // …
    
    public static function fromRequest(
        <hljs type>CustomerRequest</hljs> $request
    ): self {
        return new <hljs type>self</hljs>([
            'name' => $request-><hljs prop>get</hljs>('name'),
            'email' => $request-><hljs prop>get</hljs>('email'),
            'birth_date' => <hljs type>Carbon</hljs>::make(
                $request-><hljs prop>get</hljs>('birth_date')
            ),
        ]);
    }
}
```

Obviously, mixing application-specific code within the domain isn't the best of ideas. However, it does have my preference. There's two reasons for that.

First of all: we already established that DTOs are the entry point for data into the codebase. As soon as we're working with data from the outside, we want to convert it to a DTO. We need to do this mapping _somewhere_, so we might as well do it within the class that it's meant for.

Secondly, and this is the more important reason; I prefer this approach because one of PHP's own limitations: it doesn't support named parameters. 

See, you don't want your DTOs to end up having a constructor with an individual parameter for each property: this doesn't scale, and is very confusing when working with nullable or default-value properties. That's why I prefer the approach of passing an array to the DTO, and have it construct itself based on the data in that array. As an aside: we use our [spatie/data-transfer-object](*https://github.com/spatie/data-transfer-object) package to do exactly this.

Because named parameters aren't supported, there's also no static analysis available, meaning you're in the dark about what data is needed whenever you're constructing a DTO. I prefer to keep this "being in the dark" within the DTO class, so that it can be used without an extra thought from the outside.

If PHP were to support something like named parameters though, I would say the factory pattern is the way to go:

```php
public function fromRequest(
    <hljs type>CustomerRequest</hljs> $request
): CustomerData {
    return new <hljs type>CustomerData</hljs>(
        $name => $request-><hljs prop>get</hljs>('name'),
        $email => $request-><hljs prop>get</hljs>('email'),
        $birth_date => <hljs type>Carbon</hljs>::make(
            $request-><hljs prop>get</hljs>('birth_date')
        ),
    );
}
```

Until PHP supports this, I would choose the pragmatic solution over the theoretical correct one. It's up to you though. Feel free to choose what fits your team best.

## An alternative to typed properties

As I mentioned before, there is an alternative to using typed properties to support DTOs: docblocks. Our DTO [package](*https://github.com/spatie/data-transfer-object) I mentioned earlier also supports them.

```php
use <hljs type>Spatie\DataTransferObject\DataTransferObject</hljs>;

class CustomerData extends DataTransferObject
{
    /** @var string */
    public $name;
    
    /** @var string */
    public $email;
    
    /** @var \Carbon\Carbon */
    public $birth_date;
}
```

By default though, docblocks don't give any guarantees that the data is of the type they say it is. Luckily PHP has its reflection API, and with it, a lot more is possible.

The solution provided by this package can be thought of as an extension of PHPs type system. While there's only so much one can do in userland and at runtime, still it adds value. If you're unable to use PHP 7.4 and want a little more certainty that your docblock types are actually respected, this package has you covered.

---

Because data lives at the core of almost every project, it's one of the most important building blocks. Data transfer objects offer you a way to work with data in a structured, type safe and predictable way.

You'll note throughout this book that DTOs are used more often than not. That's why it was so important to take an in-depth look at them at the start. 
Likewise, there's another crucial building block that needs our thorough attention: actions. That's the topic for the next chapter.

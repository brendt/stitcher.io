Have you ever wondered how your life as a PHP developer would be different 
if that *one* feature you want was added?
I've made the thought experiment quite a few times already, and came to surprising conclusions.

Let's take, for example, the debate about strong types in PHP. 
A lot of people, including myself, would like a better type system. 
Strong types in PHP would definitely have an impact on my daily work. 
Not just strong types, I also want generics, better variance and variable types. 
Improvements to PHP's type system in general would have quite the impact on my programming life.

{{ ad:carbon }}

So what's stopping us from reaching a solution?

## Type theory
 
Not everyone agrees on the vocabulary used when talking about type systems. 
So let's clarify a few terms in the way that I will use them here.

**Strong or weak types** define whether a variable can change its type after it's defined. 
A simple example: say there's a variable `$a = 'test';`, which is a string;
you are able to re-assign that variable to another type, for example `$a = 1;`, an integer.

PHP is a weakly typed language, and I can illustrate this with a more real-life example:

```php
$id = '1'; // An ID retrieved as a URL parameter.

function find(int $id): Model
{
    // ...
}


find($id);
```

You might think that in modern PHP, you can avoid these problems with strict types, but that's not completely true. 
Declaring strict types prevents other types being passed into a function, 
but you can still change the value of the variable in the function itself.

```php
declare(strict_types=1);

function find(int $id): Model
{
    $id = '' . $id;

    // This is perfectly allowed in PHP: `$id` is a string now.
}

find('1'); // This would trigger a TypeError.

find(1); // This would be fine.
```

Like I said: PHP's type system is weak. 
Type hints only ensure a variable's type at that point in time, 
without a guarantee about any future value that variable might have.

Am I saying that strong types are better than weak ones? No. 
But there's an interesting property to strong types, they come with a few guarantees.
If a variable has a type that's unchangeable, a whole range of unexpected behaviour simply cannot happen anymore.

You see, it's mathematically provable that if a strongly typed program compiles,
it's impossible for that program to have a range of bugs which can exist in weakly typed languages.
In other words, strong types give the programmer a stronger insurance that the code actually behaves how it's supposed to.

This doesn't mean that a strongly typed language cannot have bugs! 
You're perfectly able to write a buggy implementation.
But when a strongly typed program compiles successfully, 
you're sure a certain set of bugs and errors can't occur in that program.

If you want to further explore the topic on strong and weak types,
I'd recommend starting with [this video](*https://www.destroyallsoftware.com/talks/ideology) by Gary Bernhardt.
Not only does it go further into detail on types, 
Gary also discusses an important mindset in the whole types debate. 

### When types are checked

We talked about **strong** and **weak** types, what about **static** and **dynamic** types? 
– This is where it starts to get truly interesting. 

As you're probably aware, PHP is an interpreted language.
This means a PHP script is compiled at runtime. 
When you send a request to a server running PHP, 
it will take those plain `.php` files, and parse the text in it to something the processor can execute.
  
This is one of PHP's strong points by the way: the simplicity on how you can write a script, 
refresh your webpage and everything is there. 
That's a big difference compared to a language that has to be compiled before it can be run. 

There is a downside though: performance.
And it's not hard to pinpoint this down: the more tasks there are to do at runtime, 
the more impact there is on performance. 
One of those many tasks the PHP engine has to take care of? Type checking.

Because PHP checks the type of variables at runtime, 
it is often described as a **dynamically typed** language.
A **statically typed** language on the other hand, 
will have all its type checks done before the code is executed.

> Hang on – I can hear you say – what does this have to do with what PHP can be?
>
> —We'll get to that.

## What this means for PHP

Now we know what we're talking about, let's take a look at PHP's type system today.

I hope that after the theory, it's clear to you that PHP is a **dynamic, weakly typed** language. 
And **there's nothing wrong with that**!

On the other hand, it's interesting to note that many people are asking for a better type system in PHP.
This doesn't mean we understand the implications of such a type system on PHP,
yet but many of us feel that *natural urge* for a better type system.
I'm sure that a lot of developers can point to real-life, 
daily situations where a better type system would actually benefit them.

To give one obvious example: the question for generics. 
Whether it is to ensure an array only contains one type of elements 
or to improve ORM abstractions, lots of people are asking for generics in PHP.

The question than becomes: is creating a more complicated type system feasible with PHP's current type paradigm? 
And the answer is, in part, yes—for sure. 
There are parts that could be improved in the current, dynamic weak type system.

Type hints for one, added in PHP 7.0 and 7.1 are useful to many PHP developers;
Levi Morrison is working on [generics in traits](*https://github.com/morrisonlevi/php-src/tree/generic_traits);
also, there are very active discussions about the type system on the internals mailing list.

**However**: we're missing a very important point.
As long as we're striving to improve PHP's runtime type system, 
we'll always be dealing with the huge performance cost it will take.

## The benefits of a static type system

This is what Rasmus Lerdorf has to say on the topic.

> Now if the RFC was a plan for baking a compile-time static analysis engine 
> into PHP itself, that would be interesting. But that is a massive project.
>
> — [Rasmus](*https://externals.io/message/101477#101592)

Imagine the possibilities when you can write PHP code that can be statically type checked 
before running the code. Tools like PHPStan and Psalm already do a lot of static analysis, 
but in my opinion it could go a step further. Say we could do this.

```php
class List<T>
{
    private array $list;
    
    // ...
}
```

What if this was valid PHP code? 
And what if the runtime engine would just plain ignore it, 
and a part of PHP engine could do all the type checks, before runtime?

That's –in my opinion– a better solution than standalone tools which rely on docblocks
and can't benefit from the core PHP engine, as they are written, in the case of Psalm and PHPStan, in PHP.

Don't get me wrong: tools like these are the first important step 
towards a bigger goal. I just think we shouldn't stop here. 

The need for a better type system is clear.
Lots of programmers experience a natural longing for something more than what's possible now.
This doesn't only happen in the PHP community, look at modern languages like Rust, 
or supersets like TypeScript for JavaScript. 

So maybe the answer for PHP lies into baked-in features in the core, 
maybe it lies in a superset that compiles to PHP with extra type checking.
That last one by the way, has already been tried: Hack on HHVM. 

There even is a third option, a question every programmer should ask themselves from time to time.
Should we want PHP to change dramatically to match our needs, 
or should we change our frame of reference, and maybe look at other languages that might fit those needs better?

There's no shame in using another tool for the job, if that tools fit your needs better.
And after all, isn't a programming language just that? A tool.

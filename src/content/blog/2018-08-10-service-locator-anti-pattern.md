As a Laravel developer, I'm confronted daily with the service locator pattern. 
Every facade call and several helper functions are built upon it.

Let's take a look at a common facade call: `Auth::user()`.
The `Auth` facade will reach into Laravel's service container, grab the registered component, 
and forward the static call to that component.
In this case, it'll return the logged in user.

{{ ad:carbon }}

During a discussion with my colleagues, I found it difficult to put into words 
what exactly is wrong with grabbing things out of the container –a service locator– so I decided to write my thoughts down, 
with an example: a class `CreatePostAction` which is tasked to create a blog post, 
based on a set of parameters.

```php
class CreatePostAction
{
    public function __invoke(
        <hljs type>string</hljs> $title, 
        <hljs type>string</hljs> $body
    ): Post
    {
        return <hljs type>Post</hljs>::<hljs prop>create</hljs>([
            'title' => $title,
            'body' => $body,
            'author_id' => <hljs type>Auth</hljs>::<hljs prop>user</hljs>()->id,
        ]);
    }
}
```

I want to highlight three problems with this approach, directly caused by the use of a service locator.

- There's a bigger chance of runtime errors.
- The code is obfuscated to the outside.
- It increases cognitive load.

Let's look at these problems, one by one.

## Runtime- instead of compile time errors

Before even looking into this first problem, there's one assumption I'll make.
That is that, as a developer, you prefer to know bugs in your code as early as possible,
so that you can fix them as early as possible.

I'll assume that you don't like a situation where a client tells you a production project is broken,
and the issue can only be reproduced by taking several steps.

As the name says, runtime errors can only be discovered by running the program.
Truth be told: ((PHP)), being an interpreted language; highly leans towards these kind of errors.
You cannot know if a ((PHP)) program will work before running it.

There's nothing wrong with that, but my argument here is that every place we can
avoid these errors, we should. 

Compile time errors are errors that can be detected without running the code. 
For example: in your ((IDE)) or using a static analysis tool.
The benefit is that you know a piece of code will absolutely work, 
even without testing it.

Let's put that into practice. What does `Auth::user()` return? 
A logged in `User`—most of the time.

Our action class doesn't know anything about the system it lives in, 
except the things we tell it. 
This means that, when calling `Auth::user()->id`, 
we assume that the surrounding system has a logged in user, with an `id`.

Of course, your first thought is that we *know* there's a user, 
because this action is called within a controller that requires a logged in user.
I'll come back to that argument later on.

For now, speaking from a mathematical point of view, 
it's impossible to prove whether `Auth::user()->id` will work, without running it.
There are two ways to fix it, from the action's perspective.

By doing a runtime check:

```php
class CreatePostAction
{
    public function __invoke(
        <hljs type>string</hljs> $title, 
        <hljs type>string</hljs> $body
    ): Post
    {
        if (! <hljs type>Auth</hljs>::<hljs prop>user</hljs>()) {
            throw new <hljs type>Exception</hljs>('…');
        }
        
        // ...
    }
}
```

Or by requiring a valid user, before executing:

```php
class CreatePostAction
{
    public function __invoke(
        <hljs type>string</hljs> $title, 
        <hljs type>string</hljs> $body,
        <hljs type>User</hljs> $author
    ): <hljs type>Post</hljs>
    {
        // ...
    }
}
```

I *know* you have arguments why this will never happen and I shouldn't be worried about it; 
I'll address those arguments soon.

## Obfuscated classes

Before looking at the biggest problem, how service locators affect cognitive load; 
there's the issue with obfuscated classes. 
Let's look at our action's definition.

```php
class CreatePostAction
{
    public function __invoke(
        <hljs type>string</hljs> $title, 
        <hljs type>string</hljs> $body
    ): Post
    { /* ... */ }
}
``` 

I've blogged and spoken about this a lot already: 
developers don't read every line of code, they scan it.

At the time of writing the code, it all seems obvious: 
you *know* a blog post requires a logged in user.
However, for the developer working in your legacy code, that intent is not clear. 
Not unless he's reading every single line of code.

Imagine being that person: having to work in a legacy project where you need to read every line of code,
in order to get the general idea of what's happening.

You might as well not be interested in the specifics of how a post is created, 
you just want to know what's required to do so. 
There's two ways to solve this issue. 

Either be using docblocks; 
meaning a lot more work for both the author and reader, and it clutters your code: 

```php
class CreatePostAction
{
    /**
     * This action will create a post, 
     * and attach the logged in user as its author.
     *
     * @param string $title
     * @param string $body
     *
     * @return Post
     */
    public function __invoke(
        <hljs type>string</hljs> $title, 
        <hljs type>string</hljs> $body
    ): Post
    { /* ... */ }
}
```

Or by injecting the user:

```php
class CreatePostAction
{
    public function __invoke(
        <hljs type>string</hljs> $title, 
        <hljs type>string</hljs> $body,
        <hljs type>User</hljs> $author
    ): Post
    { /* ... */ }
}
```

Which one do you prefer? 
Remember: from the perspective of the person working in a legacy project, 
and it's not just one class, there are dozens and dozens.

## Increased cognitive load

This all leads up to the final, and major, problem: cognitive load.
I already wrote a lot on this topic, and I'll share some links at the end of this post.

The important question, which counters most of the pro-arguments for service locators; 
is how much brain effort you, the developer, has to spend on trivial questions like:

> How sure am I this code will actually work?

Let's look at the most basic example: `Auth::user()->id`. 
I work on Laravel projects and admit to have used this piece of code numerous times. 
Here's a non-exhaustive list of questions popping into my head when writing this code:

- Am I sure a user is logged in at this point?
- Should I add an extra check, to be sure?
- What context will this method be called from?
- Are there any future features in the project's scope I need to take into account?
- Should I add a test to be sure this never breaks in the future?

These are all such trivial questions, 
and I need to think about them _every_ time I use a facade. 
How much more easy is it to simply say:

> I _need_ the logged in user to do this action, and the context which is calling this action can figure it out from there.

```php
class CreatePostAction
{
    public function __invoke(
        <hljs type>string</hljs> $title, 
        <hljs type>string</hljs> $body,
        <hljs type>User</hljs> $author
    ): Post
    { /* ... */ }
}
```

Sure, compile time errors and less code are niceties, 
but my main problem is this cognitive load. 
I don't want to ask all these questions every time I use a facade.

Seasoned Laravel developers will tell me this is the way the framework works and we should embrace it.
They are right, of course. 
But making the assumption that "it will work" isn't good enough for me.
At least, it's no argument against increased cognitive load, 
as you're still left with a lot of questions about the surrounding context.

{{ ad:google }}

## Dependency injection solves it

Dependency injection, of course; fixes this. 
It's a pattern which allows for inversion of control and clarifies intent.
It's also perfectly possible to do proper DI in Laravel; 
and, in my opinion, we should do it more.

I've written about DI before, feel free to read up on it [here](/blog/dependency-injection-for-beginners).
I also recently gave a talk about cognitive load, from a visual perspective. 
You can find it [here](/blog/visual-perception-of-code).

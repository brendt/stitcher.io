---
title: 'PHP 8.1: new in initializers'
footnotes:
    - { link: 'https://road-to-php.com/', title: 'The Road to PHP 8.1' }
    - { link: /blog/php-81-in-8-code-blocks, title: 'PHP 8.1 in 8 code blocks' }
    - { link: 'https://wiki.php.net/rfc/new_in_initializers', title: 'The new in initializers RFC' }
    - { link: /blog/php-81-readonly-properties, title: 'Readonly properties in PHP 8.1' }
    - { link: /blog/new-in-php-81, title: "What's new in PHP 8.1" }
    - { link: /blog/evolution-of-a-php-object, title: 'Evolution of objects in PHP over the years' }
    - { link: /blog/cloning-readonly-properties-in-php-81, title: 'Cloning readonly properties' }
---

PHP 8.1 adds a feature that might seem like a small detail, but one that I think will have a significant day-by-day impact on many people. So what's this "new in initializers RFC" about? Let's take a look at an example; we've all written code like this:

```php
class MyStateMachine
{
    public function __construct(
        private ?State $state = null,
    ) {
        $this->state ??= new InitialState();
    }
}
```

{{ ad:carbon }}

In this state machine example, we'd like to construct our class in two ways: _with_ and _without_ an initial state. If we construct it _without_ an initial state, we want a default one to be set. PHP of course supports setting initial values directly in the parameter list, but only for primitive types. For example, if our state machine used strings instead of objects internally, we'd be able to write its constructor like so:

```php
class MyStateMachine
{
    public function __construct(
        private string $state = 'initial',
    ) {
    }
}
```

So with PHP 8.1 we're able to use that same "default value" syntax for objects as well. In other words: you can use `new` for default arguments (which are one example of "initializers"):

```php
class MyStateMachine
{
    public function __construct(
        private State $state = new InitialState(),
    ) {
    }
}
```

"Initializers" are more than parameter default values though, here's a simple explanation from the RFC:

> This RFC proposes to allow use of new expressions inside parameter default values, attribute arguments, static variable initializers and global constant initializers

You read it right: attributes are in this list as well! Imagine a simple validation library that uses attributes to validate input on properties. Maybe it should be able to validate array elements, something like this:


```txt
class CreateEmailsRequest extends FormRequestData
{
    #[ValidArray(
        email: [new Required, new ValidEmail],
        name: [new Required, new ValidString],
    )]
    public array $people;
}
```

Before PHP 8.1, you wouldn't be able to write this kind of code, because you weren't allowed to use `new` in attributes, due to the way they are evaluated, but now you can!

Let's take a look at some important details worth mentioning.

{{ cta:dynamic }}

### Only constructed when needed

These kinds of "new values" will only be constructed when actually needed. That means that, in our first example, PHP will only create a new object of `InitialState` if no argument is given:

```php
class MyStateMachine
{
    public function __construct(
        private State $state = new InitialState(),
    ) {
    }
}

new MyStateMachine(new DraftState()); // No InitialState is created
new MyStateMachine(); // But now it is
```

In case of attributes, for example, the objects will only be created when `newInstance` is called on the [reflection attribute](/blog/attributes-in-php-8).

### Not in class properties

You should also know that you cannot use `new` as a default value in class properties. Supporting this functionality would introduce lots of unforeseen side effects when, for example, serializing and unserializing objects.

```php
class MyStateMachine
{
    private State $state = new InitialState();
}
```

Luckily we have [promoted properties](/blog/constructor-promotion-in-php-8) which do allow a default value, since PHP will transpile the property promotion syntax, keeping the default value in the constructor argument, but not in the actual property.

Here's what the transpiled version looks like:

```php
class MyStateMachine
{
    private State $state;
    
    public function __construct(
        State $state = new InitialState(),
    ) {
        $this->state = $state;
    }
}
```

### Limited input

You might have already guessed it, but you can only pass a limited set of input when constructing new objects in initializers. For example, you can't use variables, the spread operator, anonymous classes, etc. Still, it's a very welcome addition!

---

PHP is getting better and better with every update. Some people argue that these changes aren't strictly necessary since they don't add any new functionality to the language; but they _do_ make our day-by-day developer lives just a little more easy, I really like that about PHP these days!

{{ cta:like }}

{{ cta:mail }}

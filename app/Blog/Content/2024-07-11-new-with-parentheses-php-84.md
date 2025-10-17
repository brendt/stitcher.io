---
title: 'New without parentheses in PHP 8.4'
meta:
    description: 'PHP 8.4 adds a super convenient way to chain methods on newly created objects'
disableAds: true
footnotes:
    - { link: /blog/new-in-php-84, title: "What's new in PHP 8.4" }
    - { link: 'https://www.youtube.com/watch?v=3t4BxdkVL8M', title: 'Read with me through the RFC' }
    - { link: 'https://wiki.php.net/rfc/new_without_parentheses', title: 'Read the full RFC' }
---

I like syntactic sugar. You know why? It's the small changes that have the biggest impact in the long run. [Promoted properties](/blog/constructor-promotion-in-php-8), [Property hooks](/blog/new-in-php-84#property-hooks-rfc), [First class callables](/blog/new-in-php-81#first-class-callable-syntax-rfc) all had a huge impact on my day-to-day development life — and that's just a few of them. PHP 8.4 adds some more syntactic sugar, and I couldn't be more excited about it: you don't have to wrap newly created objects within parentheses anymore in order to chain methods on them.

Or, in other words, you can get rid of these two brackets:

```php
{-(-}new ReflectionClass($className){-)-}->getShortName();
```

It seems like such a small change, but I write this kind of code so often, and it's so nice that I won't have to think about wrapping stuff in brackets again. Roman [phrased it like this](https://rfc.stitcher.io/rfc/new-myclass-method-without-parentheses#pronskiy-373):

> Every time I find myself typing `new MyClass()` and then returning back and adding those parentheses. It would be good to reduce this friction and make PHP coding flow smoother.

Exactly! It's these small things that have a huge impact on a daily basis. I love it! In this post, I'm going share some of the details of this new feature, and you can read with me through the whole RFC as well:

<iframe width="560" height="345" src="https://www.youtube.com/embed/3t4BxdkVL8M" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

## Methods, properties, and more

Thanks to this new feature, you can chain much more than just methods on newly created objects: methods, properties, static methods and properties, you can use even array access or invoke the newly created class directly:

```php
new MyClass()::CONSTANT;
new MyClass()::$staticProperty;
new MyClass()::staticMethod();
new MyClass()->property;
new MyClass()->method();
new MyClass()();
new MyClass(['value'])[0];
```

Granted, something like `{php}new MyClass()()` looks a bit weird, it might just require some time to get used to, we'll see.

## Also for dynamic and anonymous classes 

The same syntax can be used on dynamic class names: class names that are resolved via a variable or via function calls:

```php
$className = MyClass::class;

new $className()->property;
new (trim(' MyClass '))->method();
```

As well as on anonymous classes:

```php
new class () { /* … */ }->method();
```

While I think shorter isn't always better, it's good to have this functionality available, just in case.

## Constructor brackets are required

There's one caveat to this feature: the new class instantiation needs to be called _with_ constructor brackets, even if no constructor parameters are passed:

```php
// This is valid PHP if MyClass has an empty constructor
$class = new MyClass; 

new MyClass{:hl-striped:->method():} // This isn't valid

new MyClass()->method() // But this is
```

The reason for this requirement is that without the constructor brackets, the PHP parser wouldn't be able to properly determine whether it's a new class instantiation or not. The [RFC](https://wiki.php.net/rfc/new_without_parentheses#why_the_proposed_syntax_is_unambiguous) explains this behaviour more in depth in order to avoid any ambiguities.

## Anonymous classes are the exception

The previous limitation however isn't present for anonymous classes, because their constructor brackets come _before_ the class' body:

```php
new class { /* … */ }->method();
new class () { /* … */ }->method();
```

Both are valid. 

---

Despite a couple of minor gotchas, I'd say this is a super nice addition to PHP, a small feature, but one with a big impact. Leave your thoughts about it in the comment section down below (I've added comments on this blog!), and don't forget to [subscribe to my mailing list](/mail) if you want to be kept in the loop!
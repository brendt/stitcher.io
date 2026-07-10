---
title: Packagist and Composer
description: Learn how modern PHP projects use Packagist and Composer to install packages, manage dependencies, configure autoloading, and share code.
image: meta/php/04-packagist-composer.png
---

Now that you know about the basic syntax and standard library of PHP, it's time to talk about PHP's strongest selling point: its ecosystem. PHP has over 30 years of history, and with that comes a vast collection of community-made open source code. Chances are that most problems you encounter, there's a package that solves it.

PHP packages are distributed via [Packagist](https://packagist.org/): the main package repository for PHP. There are other repositories available as well, and you can set up your own; for example, a private in-house repository. For most modern PHP projects, though, Packagist is the starting point. The CLI tool to pull in code from repositories is called [Composer](https://getcomposer.org/). At this point it's good to take one minute to [install Composer](https://getcomposer.org/download/) on your machine, because it's a crucial tool for any PHP developer to have.

## composer.json

There are a couple of things to explain about composer, but the most important one is `composer.json`: this file contains all information about your project: metadata, its dependencies, its requirements, autoloading configuration (we'll come back to that one), and more.

Here's what a simple `composer.json` file looks like:

```json
// composer.json

{
    "name": "brendt/stitcher.io",
    "type": "project",
    "require": {
        "php": "^8.5"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    }
}
```

First you notice the `{raw}{:hl-keyword:"name":}` property. Packagist follows a `vendor/project` naming convention, similar to how GitHub repositories are used. If you're working on a project instead of a package that should be distributed via Packagist, then the name doesn't matter all that much. I prefer to keep it consistent across all my projects, though.

Then there's the `{raw}{:hl-keyword:"type":}`, which tells Composer what kind of project this is. If omitted, the default type is `"library"`, which means this project can be used as a third-party package by other projects. `"project"`, in turn, means this is an application on its own, and that it can't be required by others.

Next, there's `{raw}{:hl-keyword:"require":}`, which contains a list of dependencies required by this project. A good practice is to always require that minimum PHP version. Apart from PHP itself, requirements can specify PHP extensions, as well as third-party packages from Packagist. 

Finally, there's `{raw}{:hl-keyword:"autoload":}`, which indicates to composer where your classes are located. It essentially maps a root namespace to a folder, and all classes within that folder are considered to live within that namespace. Note that namespace paths are separated with backslashes, which in JSON files should be escaped, hence we see `{raw}{:hl-keyword:"App\\":}` instead of `{raw}{:hl-keyword:"App\":}`. Common examples of root namespaces on the project level are `App\` or `Src\`, but you're free to choose any other name. If you're making a package meant to be used in other projects, the most common approach is to use your vendor name as the root namespace. Eg. `Tempest\` or `Brendt\`.

## Autoloading

We've mentioned autoloading a couple of times before, but how exactly does it work? Composer has an autoload config entry, and we've already discussed the meaning of the root namespace, but how exactly does it work then?

```json    
{
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    }
}
```

First, we need to understand how PHP files can reference each other. Let's say we have two files: `index.php` and `{raw}Book.php`. The `{raw}Book.php` file contains the `Book` class, and we want to use it in our `index.php`:

```php
// index.php

$book = new Book('Timeline Taxi');
```

This code won't run, because PHP doesn't know anything about our `{raw}Book.php` file. To link it, we need to `require` it into our `index.php` file:

```php
// index.php

require 'Book.php';

$book = new Book('Timeline Taxi');
```

And actually, a better practice would be to use `require_once`, so that if a file is referenced from multiple other files, PHP only has to load it once:

```php
// index.php

require_once 'Book.php';

$book = new Book('Timeline Taxi');
```

You can image how keeping manually requiring files can become tiring: for every class you want to use, you need to add a `require_once` statement to the correct file. That's the problem that autoloading solves. Let's say we have a rule that states that a class' namespace will always follow the directory structure, starting from a root. So if our root namespace is `App\`, which maps to the `app/` folder, then anything after `App\` should 1–1 map to the directory structure within `app/`.

In other words, `App\Models\Book` maps to `app/Models/Book.php`. Following that rule, we could easily automate our manual `require` statements. That's exactly what autoloading is, and what Composer does for you behind the scenes. Note that "the rule" we used to 1–1 map namespaces to directories starting from a root, is officially called "PSR-4" — "PHP Standard Recommendation 4". There's a lot to say about PSRs, which are a collection of optional standards for PHP; but we'll discuss them in a later chapter. The only thing you need to know now is that PSR-4 describes this autoloading standard, and it is what's used within virtually all of modern PHP. You can [read the details about it here](https://www.php-fig.org/psr/psr-4/).

The only thing left to do so that your codebase uses Composer's autoloader, is to `require` it. Generating Composer's autoloader is done by running `{:hl-keyword:composer:} update`:

```shell
# ~/my-project
~ composer update

# Loading composer repositories with package information
# Updating dependencies
# Writing lock file
```

Many things happen whenever you run `{:hl-keyword:composer:} update`:

- Composer will validate your `composer.json` file for any errors
- It will check all requirements and notify you when they can't be installed
- It will download all required dependencies and save them in a `vendor` folder within your project
- A lock file is written with all the current installed dependencies and their versions
- Finally, an autoload script is generated

It's this last one that we're interested in for now. Going back to our `index.php` file, we'll now require Composer's autoloader:

```php
// index.php

require_once __DIR__ . '/vendor/autoload.php';

$book = new Book('Timeline Taxi');
```

Note that we used `__DIR__`. It's not required, but good practice to use it, because `__DIR__` contains the absolute path of the current PHP file. Using it when requiring the autoloader ensures we'll always reference the right path, even when we're running our script from another directory.

We're not done yet, by the way! We're now using Composer's autoloader, but haven't switched to using namespaces yet. Let's first fix our `Book` class:

```php
// app/Models/Book.php

namespace App\Models\Book;

class Book
{
    public function __construct(
        public string $title,
    ) {}
}
```

And reference it in `index.php`:

```php
// index.php

require_once __DIR__ . '/vendor/autoload.php';

$book = new App\Models\Book('Timeline Taxi');
```

Success! We're now using composer's autoloader, and it will take care of requiring the right files for us, based on the class and its namespace. By the way, the "class + namespace" combo is officially called "Fully Qualified Class Name" — FQCN for short.

If you want to (and it's highly recommended you do), you can move all FQCNs to the start of your PHP file, and `use` them once; so that you can write short class names throughout your code instead:

```php
// index.php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Book;

$book = new Book('Timeline Taxi');
```

Also, keep in mind that you only need to require `autoload.php` only _once_ throughout your whole program. This usually happens in an index or bootstrap file. You don't need to re-require it in other classes:

```php
// app/Models/Book.php

namespace App\Models\Book;

use App\Models\Author;

class Book
{
    public function __construct(
        public string $title,
        public Author $author,
    ) {}
}
```

:::note
Note that you don't _need_ to follow PSR-4, or even use Composer. The underlying mechanism for autoloading is powered by a PHP function called [`spl_autoload_register()`](https://www.php.net/manual/en/function.spl-autoload-register.php), which you can use to build your own autoloader with. However, any modern PHP project will use Composer and PSR-4.
:::

## Dependencies

Autoloading is one thing you get when using Composer, but the other benefit is, of course, being able to tap into the vast PHP ecosystem. Composer allows you to pull in third-party software and make it available within your codebase via autoloading, but also helps you manage automatic updates and watches over any security issues that might come up in software you depend on.

There are a couple of commands to help you get started with Composer. The first one is the `require` command:

```shell
# ~/my-project
~ composer require tempest/highlight
```

Requiring a new dependency will automatically configure this package in your own `composer.json` file, find the best compatible version for you (Composer uses [Semantic Versioning](https://semver.org/)), and install it.

```json
// composer.json
{
    "require": {
        "tempest/highlight": "^2.27"
    }
}
```

Anything composer installs goes into the `vendor/` folder, and is accessible in your project via autoloading. As long as you've required `vendor/autoload.php`, you're good to go.

```php
// index.php

require_once __DIR__ . '/vendor/autoload.php';

use Tempest\Highlight\Highlighter;

$highlight = new Highlighter();
```

The next important one to cover is the `update` command:

```shell
# ~/my-project
~ composer update
```

It will loop over all your dependencies and check whether any of them have pending updates. If so, Composer will automatically pull them in for you.

Similar to `update`, there's also the `install` command:

```shell
# ~/my-project
~ composer install
```

The difference between `update` and `install` is that `install` won't run any updates if `composer.lock` is present. `composer.lock` is a file that keeps track of all installed packages together with their exact version. A common workflow is to use `install` during deployments to production, since that way you know exactly the same dependencies will be used everywhere.

There are a lot more details and options to Packagist and Composer, so if you want to learn more, you can head over to the [Composer Getting Started Guide](https://getcomposer.org/doc/01-basic-usage.md). 

## In practice

[Install Composer](https://getcomposer.org/download/) on your machine and verify that it works. In a new directory, create a PHP script that you'll use to highlight some Markdown using the [tempest/markdown](https://tempestphp.com/3.x/packages/markdown) package. It can be a fully fledged Markdown file that you read and write to the filesystem, or an in-memory string that you echo out. Up to you.

Once done, take a look at the `vendor/` folder, and try to find the `composer.json` file of `tempest/markdown` itself. What differences do you see compared to yours? Find out what these options do by [reading Composer's docs](https://getcomposer.org/doc/04-schema.md).

{{{
```json
// composer.json
{
    "require": {
        "tempest/markdown": "^1.1"
    }
}
```


```php
// index.php
<?php

use Tempest\Markdown\Markdown;

require_once __DIR__ . '/vendor/autoload.php';

$markdown = new Markdown();

$content = file_get_contents(__DIR__ . '/input.md');

if (! $content) {
    die('Something went wrong!');
}

if (file_exists(__DIR__ . '/output.html')) {
    unlink(__DIR__ . '/output.html');
}

file_put_contents(__DIR__ . '/output.html', $markdown->parse($content));
```
}}}

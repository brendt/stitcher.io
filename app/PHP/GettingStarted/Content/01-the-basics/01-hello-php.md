---
title: Hello, PHP
description: Install PHP, run your first script, use the built-in web server, and learn how modern PHP fits into web and command-line development.
image: meta/php/01-hello-php.png
---

## Intro

PHP is a general-purpose programming language with a focus on backend web development. It's powering the majority of the modern web; from blogs to webshops, knowledge bases, forums, and everything in between. A couple of well-known projects powered by PHP are [Wikipedia](https://en.wikipedia.org/wiki/Wikipedia:FAQ/Technical), [Slack](https://slack.engineering/taking-php-seriously/), [WordPress](https://wordpress.org/), and [Laravel](https://laravel.com/).

Over more than 30 years, the language and ecosystem have seen a tremendous transformation. That's why, in this book, you'll learn how to write PHP the way is done in serious software in 2026. You'll learn about the basic principles and syntax, the ecosystem and frameworks, QA tooling, and more.

### About the author

I'm Brent, and I started using PHP around 2012, right when the language was undergoing a tremendous evolution. I've worked at several PHP agencies to build anything from small NGO websites to CRMs powering whole hotel chains. I'm the author of the well-known and respected [stitcher.io](https://stitcher.io/books) blog, I've authored a handful of [books and video courses](https://stitcher.io/books), I'm currently a [developer advocate for PHP at JetBrains](https://www.jetbrains.com/guide/authors/brentroose/), where I host a [YouTube channel about PHP](https://www.youtube.com/@phpannotated), and am also building an [MVC framework for modern PHP](https://tempestphp.com/).

I'm passionate about education and find that PHP's biggest challenge in the modern web era is being well understood. That's why I'm writing this book to help anyone understand what an awesome language and ecosystem PHP is.

### Goal of this book

You don't learn a programming language by reading a book. You do so by writing code. This book won't teach you everything there is to know about PHP, rather it will teach you how to learn yourself. This book will be the foundation to build on. That's why every chapter will link to external resources for followup, and also an exercise section so that you actually write code.

:::note
I will assume you have _some_ basic knowledge of programming in general: you're either a CS student or programmer who's eager to learn PHP. In the future, this book might include a "getting started from absolute zero", but for now that's out of scope.
:::

Let's begin!

## Installing PHP

Production PHP is typically deployed using Docker, on dedicated virtual servers, or via shared hosting providers. We'll spend [a full chapter](/php/the-basics/deployment) on deploying PHP later in this book. For now, we'll focus on getting PHP running for local development. All you need is the PHP binary to get started, it will run on any operating system.

{{ download-php }}

You can find more download options on [https://www.php.net/downloads.php](https://www.php.net/downloads.php). 

## Running PHP

Once installed, you can verify whether PHP works by running:

```shell
~ php -v

# PHP 8.5.2 (cli) (built: Jan 13 2026 21:40:53) (NTS)
# Copyright (c) The PHP Group
# Built by Shivam Mathur
# Zend Engine v4.5.2, Copyright (c) Zend Technologies
#    with Zend OPcache v8.5.2, Copyright (c), by Zend Technologies
```

PHP is primarily used for web development, but that doesn't mean it's only used to serve web pages. Modern web development often involves a lot of background processing as well, or in other words: console scripting with PHP. These are the two main areas PHP will be used in: web servers and CLI applications. Production-ready web servers need a bit of setup (which is usually already provided by your hosting platform), but for local development you can serve web pages with just the PHP binary.

Let's create our first PHP file:

```php
// index.php
<?php

echo "Hello, PHP";
```

And, let's run it in CLI mode:

```shell
~ php index.php

# Hello, PHP
```

We'll dive into PHP's syntax in the next chapter; for now what's important is that this code executes! In the previous example, we've executed it in CLI mode, but what about those web pages — PHP's primary use case? For that we use [PHP's built-in web server for development](https://www.php.net/manual/en/features.commandline.webserver.php). Let's start this server in the same directory as your `index.php` file:

```shell
# ~/my-project

~ php -S localhost:8000

# PHP 8.5.2 Development Server (http://localhost:8000) started
```

If you'd now visit `localhost:8000` in your browser, you would see "Hello, PHP" as well. The reason this works out of the box is because `index.php` is treated as the _root file_ and PHP will execute that one for you. Once you start using frameworks, you'll notice that this root file might not be present, or at least not present directly in your project's folder. That's why you can explicitly tell PHP to serve a specific file as well:

```shell
~ php -S localhost:8000 ./public/boot.php
```

One thing you'll notice with PHP is that you can make changes to your source code, refresh the page, and those changes will be immediately shown. There's no recompilation or server restart required. That's because PHP is an _interpreted programming language_; its code will be compiled on the fly. Of course there are robust caching mechanisms in place to make this process very performant as well.

One of PHP's strengths is its interpreted nature. You'll find that the lack of a dedicated compilation step makes it feel very fast to develop with as you're never waiting for the compiler or restarting processes. Later in this book, we'll cover modern-day tooling for PHP that adds all kinds of "compile-time" features to the language like type checkers, code formatters, and more. What's important to note it that whenever we mention _runtime_ in PHP, it means that PHP is doing work on the fly.

## In practice

Install PHP and run `{sh}php -v` to ensure everything works. Next, run `{sh}php --help` to find out how to start an interactive shell. Try out our previous `echo` example in that shell before exiting.

Now create a new file called `hello-world.php` and add our `echo` example in it; finally access it via a web browser.

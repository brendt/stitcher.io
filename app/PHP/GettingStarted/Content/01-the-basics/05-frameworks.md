---
title: Frameworks
meta:
  title: PHP Frameworks explained
description: Compare popular PHP frameworks like Laravel, Symfony, Tempest, Laminas, and Slim, and learn when a framework helps a project.
image: meta/php/05-frameworks.png
---

These days, you usually don't write "vanilla PHP" on its own. Any serious web or console application will likely use some kind of framework to start from. That's because there is a lot of repetitive work to do when it comes to building real-life applications, and people rather focus on creating value for their customers by programming business logic, rather than lower-level code for technical support.

If you're curious about what it takes to build a framework, I have an educational video series where I built a framework from scratch during livestreams. You can [check it out here](https://www.youtube.com/playlist?list=PL0bgkxUS9EaILnUL8Q4np6B3qxjQbE7PH). On this page, I'll give you a high-level overview of the popular frameworks out there, and how they differ from each other. If you're a framework author and would like to see your project featured on this page or improve one of the existing options, you can always [submit and issue or pull request on this book's repository](https://github.com/brendt/stitcher.io/tree/main/app/PHP/GettingStarted/Content).

## In general

Most frameworks provide you with a set of common tools: HTTP routing, database support, cookie and session management, templating, validation, console scripting, etc. The difference is often the design philosophy behind these frameworks. There's no "one solution fits all", and which framework to choose depends on a number of factors like project scope, team size, personal experience, client requirements, etc.

## Laravel

Going by Packagist numbers, Laravel is by far the biggest framework in PHP these days. However, it's only fair to note that some parts of Laravel itself are also powered by lower-level Symfony components. Laravel takes a Rapid Application Development — RAD — approach to software design. Laravel users like it because it makes them feel incredibly productive with only small amounts of code. 

People who don't like Laravel say the framework can be unintuitive and often lead to lower-quality code bases when used by inexperienced developers. That being said, Laravel is used at scale for massive production projects, has been around for over a decade, and is a solid choice for most PHP projects.

You can learn how to get started with Laravel in [the Laravel docs](https://laravel.com/docs/13.x) and check out the [Laracast's 30 Days to Learn Laravel](https://www.youtube.com/watch?v=1NjOWtQ7S2o) video series.

## Symfony

Symfony has been around a little longer than Laravel, and was heavily inspired by Java Spring. Symfony is often considered a bit more complex to get started with, but also more robust when used in enterprise contexts, because of its more strict design. On top of that, most of the Symfony framework is built as standalone components, which means other projects can use them without having to pull in "the whole framework". Symfony is definitely the number one player in PHP when it comes to investing in open source and reusability.

To get started with Symfony, you can read their ["Symfony: the Fast Track"](https://symfony.com/book) book and check out [the Symfony docs](https://symfony.com/doc).

## Tempest

Tempest was created by myself as an educational experiment on how to build frameworks in modern PHP if we were to start from scratch. Many of the ideas were picked up by the community and eventually led to it to be a real project. Tempest is still very young but already used in small production websites. If you're experimenting with PHP I would say it's a very good option because it truly embraces modern PHP. People have described it as "the perfect balance between Laravel's RAD mindset and Symfony's robustness".

To get started with Tempest, you can head over to [the Tempest docs](https://tempestphp.com/3.x/getting-started/introduction), and watch [a 30-minute intro on how to get started from zero](https://www.youtube.com/watch?v=tK3u5KzDI2A).

## Laminas

Laminas is a rich history of originating from one of the earliest PHP frameworks: Zend Framework. At one point it rebranded to "Laminas" and now focuses on a "microframework" approach, to give users the benefit of flexibility to build things they want, without being coupled too tightly to a framework.

You can read more about it on [the Laminas docs](https://docs.laminas.dev/).

## WordPress

WordPress is probably the best known PHP project out there. It's not really a _framework_ in the sense that most WordPress users build their websites completely from WordPress' UI, rather than to write PHP code. Of course, there's an incredibly rich ecosystem of plugins as well (which are written in PHP), so it wouldn't be fair to not include WordPress in this list.

Where WordPres started as a CMS for blogging, it's now powering news websites to webshops and anything in between. Within modern PHP circles, WordPress is a bit infamous for being confusing and somewhat outdated from a technical point of view; but you cannot deny the success it has had over the decades around the world.

You can check out more about it on [the WordPress.org website](https://wordpress.org/).

## In practice

Choosing a framework for production projects isn't a decision you should make lightly. Given the nature of frameworks — helping you with the lower-level basics — they often require you to adhere to their own coding style and structure, making your project coupled to it forever. There are ways to prevent this, but I'm of the opinion that this coupling is a good thing that gives projects incredible productivity boosts.

If you're completely new to the world of frameworks (inside or outside of PHP), I can highly recommend [giving Tempest a try](https://tempestphp.com/3.x/getting-started/introduction), since it was created specifically for eduction in mind (and has grown into something more by now).

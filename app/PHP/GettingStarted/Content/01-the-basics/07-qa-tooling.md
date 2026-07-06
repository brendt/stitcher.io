---
title: QA Tooling
description: Learn the PHP quality tools that keep projects reliable, including static analysis, code formatting, testing, debugging, and CI checks.
image: meta/php/07-qa-tooling.png
---

A programming language is more than just its syntax and compiler. Both the ecosystem of packages and frameworks and the tooling supporting developers are equally important. This chapter will talk about that last part: quality assurance tooling.

## Static Analysis

In my opinion, static analyzers are a must in any modern-day PHP project. We've spent the [previous chapter](/php/the-basics/static-analysis) looking at them in-depth. There are different tools out there like [PHPStan](https://phpstan.org/), [Psalm](https://psalm.dev/) [Phan](https://github.com/phan/phan), or [Mago](https://mago.carthage.software/). These tools mostly differ in the details: different configuration ways, different niche inspections and type annotations, and performance as well.

I'd say all these tools are valid choices, and you should take a look at all of them to find out what works best for you in your project. My personal choice these days is Mago, because it's much faster and combines all kinds of static analysis tools into one, but tools like PHPStan are older with a more established community around them.

## Code styling

Consistency is key, that's also true for code styling. Not worrying about how and where to place brackets and colons will help any team focus on the things that truly matter when coding.

When it comes to ensuring consistent coding styles, there are two options: [PHP CS Fixer](https://github.com/php-cs-fixer/php-cs-fixer) originated from Symfony, is written in PHP, and has been around for years and years. The alternative is [Mago](https://mago.carthage.software/). Mago actually aims to be a whole toolchain for PHP, doing analysis, formatting, and linting. We've already seen that Mago is written in Rust, and thus considerably faster than PHP CS Fixer.

Whichever tool you prefer, you still need to know about which coding standard to follow. For the longest time there was [PSR-12](https://www.php-fig.org/psr/psr-12/) (created by the same group that made the autoloading standard). A couple of years ago we got an update on PSR-12 called [PER Coding Style](https://www.php-fig.org/per/coding-style/). Both Mago and PHP CS Fixer come with presets for these two. 

That being said, in this day and age coding style standards aren't as important anymore as they used to be: modern PHP projects pick a standard and enforce it during their CI process, ensuring all code ending up on team member's machines is styled consistently.

My personal preference is to start from PSR-12 and use a couple of custom rules throughout all my open source code, which I enforce with Mago via GitHub actions.

## Testing

For ages, the de-facto standard testing framework in PHP has been [PHPUnit](https://phpunit.de/index.html). It's a battle-tested testing framework used by millions. There are also some higher-level testing frameworks like [PHPSpec](https://phpspec.net/en/stable/) and [Behat](https://docs.behat.org/en/latest/).

A couple of years ago, a new testing framework called [Pest](https://pestphp.com/) was built on top of PHPUnit. Pest is more inspired by JavaScript-style testing frameworks and takes a more functional approach to testing compared to the classic class-based design of PHPUnit. Both Pest and PHPUnit are solid options though, with a thriving community behind each.

## Debugging

Step debugging in PHP is usually done with [Xdebug](https://xdebug.org/), combined with built-in support in [PhpStorm](https://www.jetbrains.com/help/phpstorm/configuring-xdebug.html). I made a starter guide for debugging in PhpStorm, which you can watch here:

{{ yt:ssZH94UfY6A }}

That said, many PHP developers prefer to use simple "dump-and-die debugging" approach with tools like [`symfony/var-dumper`](https://packagist.org/packages/symfony/var-dumper) or [`larapack/dd`](https://packagist.org/packages/larapack/dd). Tools like [Ray](https://spatie.be/products/ray) build on top of the simple "dump-and-die debugging" approach with a clean interface as well.

## In practice

Challenge yourself to get familiar with a tool from each category. My personal tooling stack is Mago, PHPUnit, and `symfony/var-dumper`; combined with Xdebug when needed. Don't worry about understanding the ins and outs of these tools, but at least make sure you know how to install them and how to use the basics.

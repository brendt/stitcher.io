---
title: QA Tooling
description: Learn the PHP quality tools that keep projects reliable, including static analysis, code formatting, testing, debugging, and CI checks.
image: meta/php/06-qa-tooling.png
---

These days, a programming language is more than just its syntax and compiler. Both the ecosystem of packages and frameworks and the tooling supporting developers are equally important. This chapter will talk about that last part: quality assurance tooling.

## Static Analysis

We've already touched on PHP's type system in [chapter 2](/php/the-basics/basic-syntax#types) where we explained how PHP has type annotations in many places, which are checked at runtime. As you can imagine, runtime type checks are expensive, especially since a type checker generally can guarantee correctness without running a single line of code. The details of the how's, why's, and pro's of type checking would lead us too far, so we'll focus on the available options in PHP these days.

Around 10 years ago, developers started building the first type checker for PHP. These days, there are a number of full-fledged static analyzers out there: tools that analyse your codebase without running the code, detecting problems along the way. 

The most popular option currently is [PHPStan](https://phpstan.org/), a static analyzer written in PHP itself. Other options are [Psalm](https://psalm.dev/) or [Phan](https://github.com/phan/phan), and the most recent addition is [Mago](https://mago.carthage.software/). What sets Mago apart is that it's written in Rust, making it a lot faster. Mago also includes a more varied set of static checks and code formatters.

Lastly, it's not unimportant to mention that all these tools are built around the idea that they analyze your codebase as a whole, in bulk. Real-time analysis as you're coding is equally important, and for that my (and many other's) preference is [PhpStorm](https://www.jetbrains.com/phpstorm/). Another popular option is [VSCode](https://code.visualstudio.com/) with the Intelephense plugin, but truth be told nothing can match PhpStorm's static capabilities for PHP.

## Code styling

Consistency is key, that's also true for code styling. Not worrying about how and where to place brackets and colons will help any team focus on the things that truly matter when coding.

When it comes to ensuring consistent coding styles, there are two options: [PHP CS Fixer](https://github.com/php-cs-fixer/php-cs-fixer) originated from Symfony, is written in PHP, and has been around for years and years. The alternative is [Mago](https://mago.carthage.software/) (the same one as mentioned in the static analysis section). Mago actually aims to be a whole toolchain for PHP, doing analysis, formatting, and linting. We've already seen that Mago is written in Rust, and thus considerably faster than PHP CS Fixer.

Whichever tool you prefer, you still need to know about which coding standard to follow. For the longest time there was [PSR-12](https://www.php-fig.org/psr/psr-12/) (created by the same group that made the autoloading standard). A couple of years ago we got an update on PSR-12 called [PER Coding Style](https://www.php-fig.org/per/coding-style/). Both Mago and PHP CS Fixer come with presets for these two. 

That being said, in this day and age coding style standards aren't as important anymore as they used to be: modern PHP projects pick a standard and enforce it during their CI process, ensuring all code ending up on team member's machines is styled consistently.

My personal preference is to start from PSR-12 and use a couple of custom rules throughout all my open source code, which I enforce with Mago via GitHub actions.

## Testing

For ages, the de-facto standard testing framework in PHP has been [PHPUnit](https://phpunit.de/index.html). It's a battle-tested testing framework used by millions. There are also some higher-level testing frameworks like [PHPSpec](https://phpspec.net/en/stable/) and [Behat](https://docs.behat.org/en/latest/).

A couple of years ago, a new testing framework called [Pest](https://pestphp.com/) was built on top of PHPUnit. Pest is more inspired by JavaScript-style testing frameworks and takes a more functional approach to testing compared to the classic class-based design of PHPUnit. Both Pest and PHPUnit are solid options though, with a thriving community behind each.

## Debugging

Step debugging in PHP is usually done with [Xdebug](https://xdebug.org/), combined with built-in support in [PhpStorm](https://www.jetbrains.com/help/phpstorm/configuring-xdebug.html).

That said, many PHP developers prefer to use simple "dump-and-die debugging" approach with tools like [`symfony/var-dumper`](https://packagist.org/packages/symfony/var-dumper) or [`larapack/dd`](https://packagist.org/packages/larapack/dd). Tools like [Ray](https://spatie.be/products/ray) build on top of the simple "dump-and-die debugging" approach with a clean interface as well.

## In practice

Challenge yourself to get familiar with a tool from each category. My personal tooling stack is Mago, PHPUnit, and `symfony/var-dumper`; combined with Xdebug when needed. Don't worry about understanding the ins and outs of these tools, but at least make sure you know how to install them and how to use the basics.

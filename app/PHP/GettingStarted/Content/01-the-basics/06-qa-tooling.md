---
title: QA Tooling
---

These days, a programming language is more than just its syntax and compiler. Both the ecosystem of packages and frameworks and the tooling supporting developers are equally important. This chapter will talk about that last part: quality assurance tooling.

## Static Analysis

We've already touched on PHP's type system in [chapter 2](/php/the-basics/basic-syntax#types) where we explained how PHP has type annotations in many places, which are checked at runtime. As you can imagine, runtime type checks are expensive, especially since a type checker generally can guarantee correctness without running a single line of code. The details of the how's, why's, and pro's of type checking would lead us too far, so we'll focus on the available options in PHP these days.

Around 10 years ago, developers started building the first type checker for PHP. These days, there are a number of full-fledged static analyzers out there: tools that analyse your codebase without running the code, detecting problems along the way. 

The most popular option currently is [PHPStan](https://phpstan.org/), a static analyzer written in PHP itself. Other options are [Psalm](https://psalm.dev/) or [Phan](https://github.com/phan/phan), and the most recent addition is [Mago](https://mago.carthage.software/). What sets Mago apart is that it's written in Rust, making it a lot faster. Mago also includes a more varied set of static checks and code formatters.

Lastly, it's not unimportant to mention that all these tools are built around the idea that they analyze your codebase as a whole, in bulk. Real-time analysis as you're coding is equally important, and for that my (and many other's) preference is [PhpStorm](https://www.jetbrains.com/phpstorm/). Another popular option is [VSCode](https://code.visualstudio.com/) with the Intelephense plugin, but truth be told nothing can match PhpStorm's static capabilities for PHP.

## Code styling

## Testing

## Debugging
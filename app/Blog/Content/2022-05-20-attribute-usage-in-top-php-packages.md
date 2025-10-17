---
title: 'Attribute usage in top-1000 PHP packages'
meta:
    description: 'I analysed the top-1000 most popular PHP packages to see how they use attributes'
---

Attributes were originally [introduced in PHP 8.0](/blog/attributes-in-php-8) two years ago. Yesterday, I came across an interesting [post from Exakat](https://www.exakat.io/en/adoption-of-php-8-attributes-in-2022/): they analysed 535 open source packages to see which attributes were used the most often.

However: they didn't share their raw data or absolute numbers, and they also didn't mention which packages were analysed exactly and how. 

I think we can do better. So let's take a closer look at how attributes are used in the PHP open source community.

{{ ad:carbon }}

## Setup

I used Nikita's [Popular Package Analyser](https://github.com/nikic/popular-package-analysis): it downloads the top-1000 packages from Packagist, and allows you to run analysis on them by writing simple PHP scripts. 

In this case I looped over all PHP files, and scanned whether they contained attributes. You can [check out the full script here](https://gist.github.com/brendt/09026efba38a2eae952556aa274c268f).

A couple of remarks:

- This is a quick and dirty script, it gets the job done and that's all it should do.
- I filtered out JetBrains packages, because they only contain stubs for IDE usage, and heavily skew the result: JetBrains' packages alone account for ±65% of the total attribute usage.
- Matching is done with a simple regex, I believe I took every edge case into account: names with and without backslashes and multiline and single-line attributes. If you believe there's something missing, don't hesitate to [let me know](mailto:brendt@stitcher.io).
- You can [browse the raw data here](https://docs.google.com/spreadsheets/d/1-JvMJcpArIMYN6NMV5NFm1hbWYcNYg4-AGmDi-t6lsI/edit?usp=sharing).
- We should recognise the limits of this data set: client projects will probably use much more attributes compared to reusable packages. Think about Symfony's route attributes for example: they will only represent a very small share in this analysis, while it's reasonable to assume they are use significantly more in real projects.

## The most popular attribute

The first metric that stood out is that `#[<hljs type>\ReturnTypeWillChange</hljs>]` outnumbers all others by far: out of **2786 attributes in total**, 1668 are either `#[<hljs type>\ReturnTypeWillChange</hljs>]` or `#[<hljs type>ReturnTypeWillChange</hljs>]`, that's **almost 60% being `<hljs type>ReturnTypeWillChange</hljs>` attributes**.

[![](/img/blog/attributes-stats/attributes.svg)](/img/blog/attributes-stats/attributes.svg)

This attribute is the first built-in attribute in PHP, introduced in PHP 8.1, and meant to [deal with a newly added deprecation notice](/blog/dealing-with-deprecations). It was a design goal of the original attribute RFC to use them for these kinds of cases where we want userland code to communicate information to PHP's interpreter. In this case: to suppress a deprecation notice.

Speaking of the two variants (with and without a leading `\`): some developers import their attributes and others use the fully qualified class name: the latter option is by far the preferred one: out of **1668 `<hljs type>ReturnTypeWillChange</hljs>` usages**, only **524 imported** the attribute — that's only **31%**. It seems that most developers like to use the FQCN variant.

{{ cta:dynamic }}

## The most active packages

Out of 997 packages, **only 200 packages are using attributes**. Out of those 200, Symfony, Drupal and Laravel have large pieces of the pie. Symfony is the leader here with **9.6%**: not unlikely caused by the fact that Symfony has been using docblock annotations for years.

[![](/img/blog/attributes-stats/attributes-per-package.svg)](/img/blog/attributes-stats/attributes-per-package.svg)

## Custom attributes

Another point of interest is the use of `#[<hljs type>Attribute</hljs>]` and `#[<hljs type>\Attribute</hljs>]`: representing custom attributes provided by packages themselves. In total there are **561 custom attributes** provided by these packages.

Looking on a per-package basis: **Symfony provides 88 custom attributes**, with PHPUnit providing 49, and doctrine's mongodb implementation providing 42. Again a clear example of how Symfony is an early adopter, thanks to them being used to docblock annotations for years. Interestingly, **Laravel provides no custom attributes**.

## Multiline attributes

It's remarkable that there are no vendors using multiline attributes:

```php
<hljs comment>#[
    <hljs type>AsCommand</hljs>,
    <hljs type>ReturnTypeWillChange</hljs>,
]</hljs>
```

It makes sense that vendors opt for the single-line notation, since it's compatible with older PHP versions because these lines are treated like comments before PHP 8.0:

```php
#[<hljs type>AsCommand</hljs>]
#[<hljs type>ReturnTypeWillChange</hljs>]
```

## Conclusions

- Only **20%** of the top-1000 most popular packages use attributes
- Only **23%** of the top-1000 most popular packages have PHP 8.0 or higher as their minimum required version
- The `<hljs type>ReturnTypeWillChange</hljs>` attribute is by far the most used one
- **Symfony clearly is the frontrunner**, embracing attributes thanks to experience with docblock annotations in the past
- **Laravel provides no custom attributes** for their users, although they use some internally, mostly the `<hljs type>AsCommand</hljs>` attribute, which is provided by Symfony

On a personal note: I'd say there's room for improvement. I think Laravel should start embracing custom attributes, especially since they now require PHP 8.0 as their minimum version.

What's your opinion? Let me know via [Twitter](*https://twitter.com/brendt_gd) or [email](mailto:brendt@stitcher.io)!

{{ cta:like }}

{{ cta:mail }}

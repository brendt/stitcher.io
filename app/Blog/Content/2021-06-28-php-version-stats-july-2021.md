---
title: 'PHP version stats: July, 2021'
versionStatsDate: 1625097600
meta:
    description: "Insights in PHP's version usage"
    template: blog/meta/version-stats.twig
footnotes:
    - { link: /blog/php-81-in-8-code-blocks, title: 'PHP 8.1 in 8 code blocks' }
    - { title: 'Live version data on Packagist', link: 'https://packagist.org/php-statistics' }
    - { title: 'PHP in 2021', link: /blog/php-in-2021 }
    - { title: "What's new in PHP 8.1", link: /blog/new-in-php-81 }
    - { title: "What's new in PHP 8", link: /blog/new-in-php-8 }
    - { title: "What's new in PHP 7.4", link: /blog/new-in-php-74 }
---

Last month, Jordi announced he'd be [sunsetting](*https://blog.packagist.com/sunsetting-the-php-version-stats-blog-series/) his PHP Version Stats blog series, so that he can focus on other types of content. As a replacement, he made [a dashboard](*https://packagist.org/php-statistics) with live version stats that you can check out any time you want.

I made sure to let Jordi know how much I appreciated all the work he put into this, but also told him I felt kind of sad the series would end. While real-time stats are definitely useful, I did enjoy the occasional post that popped up every 6 months or so. They were a good interpretation of the data, and always sparked interesting discussions. 

So I asked Jordi if he'd be ok with me continuing the series, which he was. And so, here we are, with a brand new version stats update! 

I will keep a slightly different schedule though: I'll post in July and January, because I'm interested in seeing early adaption of the newest PHP version a month or so after it's released (which is usually at the end of November). I admit I'm two days early with this one, that's because of some upcoming holiday plans.
And one more disclaimer: the [data](*https://packagist.org/php-statistics) used in this post only looks at the composer ecosystem, and while composer represents a large part of the PHP community, it doesn't represent the whole.

With all of that being said, let's dive in!

---

## Usage Statistics

Let's start with the raw numbers: the percentage of PHP versions being used:

<table>

<tr class="table-head">
    <td>Version</td>
    <td>%</td>
</tr>

<tr>
    <td>8.0</td>
    <td>14.7</td>
</tr>

<tr>
    <td>7.4</td>
    <td>46.8</td>
</tr>

<tr>
    <td>7.3</td>
    <td>19.2</td>
</tr>

<tr>
    <td>7.2</td>
    <td>10.4</td>
</tr>

<tr>
    <td>7.1</td>
    <td>3.8</td>
</tr>

<tr>
    <td>7.0</td>
    <td>1.3</td>
</tr>

<tr>
    <td>5.6</td>
    <td>1.1</td>
</tr>

</table>

Note that I've excluded versions with less than 1% usage, in practice this means everything older than 5.6, as well as 8.1 (which hasn't been released yet) is excluded. Let's visualise this year's data and compare it to last year's:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2021-july-01.svg)](/resources/img/blog/version-stats/2021-july-01.svg)

<em class="center small">[Evolution of version usage, now and in May, 2020](/resources/img/blog/version-stats/2021-july-01.svg)</em>

PHP 7.4 has seen significant growth compared to last year. In fact, it looks like many people went straight from older versions to 7.4. Let's hope we'll be able to observe the same trend next year for PHP 8. Major version updates tend to take a little longer though; I assume that's because developers are wary of the breaking changes associated with them. Good news though: the update from PHP 7.4 to PHP 8 is actually surprisingly easy, thanks to the deprecation work that has been done in the 7.x series. It might be worth considering updating soon, not only because active support for PHP 7.4 ends in [less than half a year](*https://www.php.net/supported-versions.php), but also because PHP 8 brings tons of [new and useful features](/blog/new-in-php-8). 

{{ cta:mail }}

Next, let's take a look at the all-time evolution chart, which also clearly visualizes the growth of PHP 7.4:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2021-july-02.svg)](/resources/img/blog/version-stats/2021-july-02.svg)

<em class="center small">[All time evolution](/resources/img/blog/version-stats/2021-july-02.svg)</em>

I want to emphasize once more that the update from PHP 7.4 to 8 isn't all that hard, and truly worth investing a little time in. There are tools like [Rector](*https://getrector.org/) and [PHP CS Fixer](*https://github.com/FriendsOfPHP/PHP-CS-Fixer) that can help make the upgrade even more smooth.

## Required versions

I've used Nikita's [popular package analyzer](*https://github.com/nikic/popular-package-analysis) to download the 1000 most popular composer packages, and wrote a little script to get the lowest version they support from their `composer.json`. Here are the results:

<table>

<tr class="table-head">
    <td>Version</td>
    <td>#</td>
</tr>

<tr>
    <td>8.0</td>
    <td>117</td>
</tr>

<tr>
    <td>7.4</td>
    <td>56</td>
</tr>

<tr>
    <td>7.3</td>
    <td>133</td>
</tr>

<tr>
    <td>7.2</td>
    <td>142</td>
</tr>

<tr>
    <td>7.1</td>
    <td>182</td>
</tr>

<tr>
    <td>7.0</td>
    <td>31</td>
</tr>

<tr>
    <td>5.6</td>
    <td>61</td>
</tr>

<tr>
    <td>5.5</td>
    <td>43</td>
</tr>

<tr>
    <td>5.4</td>
    <td>41</td>
</tr>

<tr>
    <td>5.3</td>
    <td>97</td>
</tr>

<tr>
    <td>5.2</td>
    <td>12</td>
</tr>

<tr>
    <td>5.0</td>
    <td>2</td>
</tr>

</table>

You'll notice there are a little less than 1000 packages included, that's because some of the 1000 most popular packages don't specifically require a PHP version.

It's interesting how 7.1 is still the minimum required version for almost 20% of the analysed packages. 7.2 adds another 15%. In fact, **only 18% of the most popular packages require an actively supported version as their minimum**.  

I'm an open [source maintainer](https://spatie.be/open-source?search=&sort=-downloads) myself, and I believe we have a responsibility to the community to help keep their software stack safe and secure. This starts with only supporting active PHP versions. So I hope to see this number shift more towards PHP 8 in the near future because, remember, PHP 7.4 is already nearing its end!

That being said, it's clear that many users _are_ moving towards a supported PHP version, despite lower package requirements. I am a little worried about the move to PHP 8 though, and I hope we'll see broader adoption soon.

---

What are your thoughts on these stats? Are you looking forward to [PHP 8.1](/blog/new-in-php-81)? Let me know your thoughts on [Twitter](*https://twitter.com/brendt_gd) and subscribe to [my newsletter](/newsletter/subscribe) if you want to be kept up-to-date about these posts!

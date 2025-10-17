---
title: 'PHP version stats: July, 2022'
versionStatsDate: 1656633600
meta:
    description: "Insights in PHP's version usage"
    template: blog/meta/version-stats.twig
footnotes:
    - { link: /blog/new-in-php-82, title: "What's new in PHP 8.2" }
    - { link: 'https://packagist.org/php-statistics', title: 'Raw data from packagist' }
    - { link: /blog/php-81-in-8-code-blocks, title: 'PHP 8.1 in 8 code blocks' }
    - { title: 'PHP version stats: January, 2022', link: /blog/php-version-stats-january-2022 }
    - { title: 'PHP in 2022', link: /blog/php-in-2022 }
---

It's that time again: my biyearly summary of which PHP versions are used across the community. I know I'm a _little_ early, that's because I had some spare time today and wanted to make sure I got it ready in time. You can read the January edition [here](/blog/php-version-stats-january-2022).

As always, it's important to note that I'm working with the data available to us. That means that these charts are no 100% accurate representation of the PHP community as a whole, but they _are_ an accurate representation of one of the most prominent parts of PHP: the [packagist ecosystem](https://packagist.org/php-statistics).

{{ ad:carbon }}

## Usage Statistics

Let's start with the percentage of PHP versions being used today, and compare it to the previous two editions:

<table>

<tr class="table-head">
    <td>Version</td>
    <td>July, 2021 (%)</td>
    <td>January, 2022 (%)</td>
    <td>July, 2022 (%)</td>
</tr>

<tr>
    <td>8.1</td>
    <td>0.1</td>
    <td>9.1</td>
    <td>24.5</td>
</tr>

<tr>
    <td>8.0</td>
    <td>14.7</td>
    <td>23.9</td>
    <td>20.6</td>
</tr>

<tr>
    <td>7.4</td>
    <td>46.8</td>
    <td>43.9</td>
    <td>38.4</td>
</tr>

<tr>
    <td>7.3</td>
    <td>19.2</td>
    <td>12.0</td>
    <td>8.0</td>
</tr>

<tr>
    <td>7.2</td>
    <td>10.4</td>
    <td>6.6</td>
    <td>5.1</td>
</tr>

<tr>
    <td>7.1</td>
    <td>3.8</td>
    <td>2.4</td>
    <td>1.9</td>
</tr>

</table>

Note that I've omitted all versions that don't have more than 1% usage. Visualizing this data looks something like this:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2022-july-01.svg)](/resources/img/blog/version-stats/2022-july-01.svg)

<em class="center small">[Evolution of version usage](/resources/img/blog/version-stats/2022-july-01.svg)</em>

As expected during a year with a minor release instead of a major one: PHP 8.1 is growing, and PHP 8.0's usage is already declining. A good sign that developers are updating! Keep in mind that PHP 8.0 is still actively [supported for another four months](*https://www.php.net/supported-versions.php). So if you kept off on updating to PHP 8.1, now is a good time.

Less good news — although not unexpected: more than 50% of developers are still on PHP 7.4 or lower. That's not an insignificant number, considering that PHP 7.4 only receives security updates for 5 more months, and all older versions simply aren't supported anymore.

I did hope to see PHP 8.X adoption to be climbing more rapidly, I've shared some of my thoughts about it [here](/blog/a-storm-in-a-glass-of-water), if you want some more reading.

{{ cta:dynamic }}

Moving on the the all-time overview chart, here you can see the evolution of version usage across time:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2022-july-02.svg)](/resources/img/blog/version-stats/2022-july-02.svg)

<em class="center small">[All time evolution](/resources/img/blog/version-stats/2022-july-02.svg)</em>

It's interesting to compare the 5.5 peak in 2014 to the 7.4 peak two years ago. PHP 5.5 and subsequent versions saw a much faster decline as soon as PHP 7.0 became available, compared to PHP 7.4's decline when PHP 8.0 was released. I'm a little worried that PHP 8.0 wasn't _as_ exciting as PHP 7.0 back in the day.  

Fear for upgrading shouldn't be a blocker these days compared to eight years ago: we now have mature tools like [Rector](https://github.com/rectorphp/rector) and [PHP CS](https://github.com/squizlabs/PHP_CodeSniffer) that take care of almost the whole upgrade path for you. 

So why aren't people upgrading to PHP 8.0? Why are more people staying with PHP 7.4 compared to the 5.5 and 5.6 days? I don't have a definitive answer.

## Required versions

Part of the answer though (I think) lies with the open source community: what are packages requiring as their minimal version? Are they encouraging their users to update, or not?

I used Nikita's [popular package analyzer](*https://github.com/nikic/popular-package-analysis) to download the 1000 most popular composer packages. Next, I used a little script to get the lowest version each package supports from their `composer.json` file. Here are the results:

<table>

<tr class="table-head">
    <td>Version</td>
    <td>July, 2021 (#)</td>
    <td>January, 2022 (#)</td>
    <td>July, 2022 (#)</td>
</tr>

<tr>
    <td>8.1</td>
    <td>-</td>
    <td>-</td>
    <td>125</td>
</tr>

<tr>
    <td>8.0</td>
    <td>117</td>
    <td>160</td>
    <td>94</td>
</tr>

<tr>
    <td>7.4</td>
    <td>56</td>
    <td>69</td>
    <td>86</td>
</tr>

<tr>
    <td>7.3</td>
    <td>133</td>
    <td>116</td>
    <td>104</td>
</tr>

<tr>
    <td>7.2</td>
    <td>142</td>
    <td>133</td>
    <td>130</td>
</tr>

<tr>
    <td>7.1</td>
    <td>182</td>
    <td>190</td>
    <td>153</td>
</tr>

<tr>
    <td>7.0</td>
    <td>31</td>
    <td>29</td>
    <td>29</td>
</tr>

<tr>
    <td>5.6</td>
    <td>61</td>
    <td>49</td>
    <td>42</td>
</tr>

<tr>
    <td>5.5</td>
    <td>43</td>
    <td>42</td>
    <td>35</td>
</tr>

<tr>
    <td>5.4</td>
    <td>41</td>
    <td>43</td>
    <td>40</td>
</tr>

<tr>
    <td>5.3</td>
    <td>97</td>
    <td>83</td>
    <td>77</td>
</tr>

<tr>
    <td>5.2</td>
    <td>12</td>
    <td>10</td>
    <td>10</td>
</tr>

<tr>
    <td>5.0</td>
    <td>2</td>
    <td>2</td>
    <td>1</td>
</tr>

</table>

I have mixed feelings about this data. On the one hand it's good to see PHP 8.1 as the minimum required version for 125 packages. However, look at how many packages still require a version lower than PHP 8.0: 707 out of 926 packages analysed. That's more than 75%!

Oh, as a side note: there only are 926 packages because some of the 1000 most popular packages don't specifically require a PHP version. 

Let's plot this data into a chart:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2022-july-03.svg)](/resources/img/blog/version-stats/2022-july-03.svg)

<em class="center small">[Minimal PHP requirement over time](/resources/img/blog/version-stats/2022-july-03.svg)</em>

I won't say that the open source community is the only responsible factor here, but I do want to encourage you to think carefully about your responsibilities if you are an open source maintainer. We're not just talking about new and shiny PHP features here: we're talking about performance, software security for the most popular programming language on the web, and even about the impact of old PHP versions on electricity usage and server requirements, in Rasmus' words we can help [save the planet](https://youtu.be/fYTKm2oUzAg?t=617).

---

What are your thoughts on these stats? Are you already using [PHP 8.1](/blog/new-in-php-81)? Let me know your thoughts on [Twitter](*https://twitter.com/brendt_gd) and subscribe to [my newsletter](/newsletter/subscribe) if you want to be kept up-to-date about these posts!

{{ cta:like }}

{{ cta:mail }}

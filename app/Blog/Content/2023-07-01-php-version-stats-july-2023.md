---
title: 'PHP version stats: July, 2023'
versionStatsDate: 1688169600
disableAds: true
meta:
    description: "Insights in PHP's version usage"
    template: blog/meta/version-stats.twig
footnotes:
    - { link: /blog/new-in-php-83, title: "What's new in PHP 8.3" }
    - { link: 'https://packagist.org/php-statistics', title: 'Raw data from packagist' }
    - { title: 'PHP version stats: January, 2023', link: /blog/php-version-stats-january-2023 }
    - { title: 'PHP in 2023', link: /blog/php-in-2023 }
---

Once again, I'm writing my summary of which PHP versions are used across the community. You can read the previous edition [here](/blog/php-version-stats-january-2023), but I'll also include historic data in this post.

As always, it's important to note that I'm working with the data available to us. That means that these charts are not a 100% accurate representation of the PHP community as a whole, but they _are_ an accurate representation of one of the most prominent parts of PHP: the [packagist ecosystem](https://packagist.org/php-statistics).

{{ cta:packagist }}

## Usage Statistics

Let's start with the percentage of PHP versions being used today, and compare it to the previous three editions, note that I've omitted all versions that don't have more than 1% usage:

<div class="table-container">
<table>

<tr class="table-head">
    <td>Version</td>
    <td>2022-01</td>
    <td>2022-07</td>
    <td>2023-01</td>
    <td>2023-07</td>
</tr>


<tr>
    <td>7.1</td>
    <td>2.4%</td>
    <td>1.9%</td>
    <td>1.8%</td>
    <td>1.3%</td>
</tr>

<tr>
    <td>7.2</td>
    <td>6.6%</td>
    <td>5.1%</td>
    <td>4.3%</td>
    <td>4.3%</td>
</tr>

<tr>
    <td>7.3</td>
    <td>12.0%</td>
    <td>8.0%</td>
    <td>5.3%</td>
    <td>4.2%</td>
</tr>

<tr>
    <td>7.4</td>
    <td>43.9%</td>
    <td>38.4%</td>
    <td>27.7%</td>
    <td>19.9%</td>
</tr>

<tr>
    <td>8.0</td>
    <td>23.9%</td>
    <td>20.6%</td>
    <td>16.2%</td>
    <td>12.3%</td>
</tr>

<tr>
    <td>8.1</td>
    <td>9.1%</td>
    <td>24.5%</td>
    <td>38.8%</td>
    <td>39.3%</td>
</tr>

<tr>
    <td>8.2</td>
    <td>0.0%</td>
    <td>0.0%</td>
    <td>4.7%</td>
    <td>17.2%</td>
</tr>

</table>
</div>

Visualizing this data looks like this:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2023-jul-01.svg)](/resources/img/blog/version-stats/2023-jul-01.svg)

<em class="center small">[Evolution of version usage](/resources/img/blog/version-stats/2023-jul-01.svg)</em>

It's important to know which PHP versions are currently still supported: PHP 8.2 and PHP 8.1 are still receiving updates. PHP 8.0 is still getting security updates until the end of November, this year. That means that PHP 7.4 and below don't receive any updates more, and should be considered end of life.

In total, **that's around 30% of packagist downloads by outdated and insecure version of PHP**. At the beginning of this year, that number was closer to 40%, meaning we see a steady decline — a good thing!

{{ cta:dynamic }}

Moving on to the all-time overview chart, here you can see the evolution of version usage across time:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2023-jul-02.svg)](/resources/img/blog/version-stats/2023-jul-02.svg)

<em class="center small">[All time evolution](/resources/img/blog/version-stats/2023-jul-02.svg)</em>

It seems that **PHP 8.1 saw the biggest growth over time since PHP 7.4 and PHP 5.5**. PHP 8.2, in comparison, seems to make a slower start. It's also interesting to note a relative high percentage of PHP 8.1 two years in a row. Granted, PHP 8.1 was a pretty solid release with [features like enums and readonly properties](/blog/new-in-php-81). It'll be interesting to see how this graph evolves next year, when PHP 8.1 moves in security fixes only mode.  


{{ cta:packagist }}

## Required versions

Next, I used Nikita's [popular package analyzer](*https://github.com/nikic/popular-package-analysis) to download the 1000 most popular composer packages. I wrote a script that scans these packages to determine their minimum required version. Here are the results:

<div class="table-container">
<table>

<tr class="table-head">
    <td>Version</td>
    <td>2022-01</td>
    <td>2022-07</td>
    <td>2023-01</td>
    <td>2023-07</td>
</tr>

<tr>
    <td>5.2</td>
    <td>10</td>
    <td>10</td>
    <td>10</td>
    <td>7</td>
</tr>

<tr>
    <td>5.3</td>
    <td>83</td>
    <td>77</td>
    <td>78</td>
    <td>65</td>
</tr>

<tr>
    <td>5.4</td>
    <td>43</td>
    <td>40</td>
    <td>40</td>
    <td>31</td>
</tr>

<tr>
    <td>5.5</td>
    <td>42</td>
    <td>35</td>
    <td>37</td>
    <td>21</td>
</tr>

<tr>
    <td>5.6</td>
    <td>49</td>
    <td>42</td>
    <td>43</td>
    <td>32</td>
</tr>

<tr>
    <td>7.0</td>
    <td>29</td>
    <td>29</td>
    <td>30</td>
    <td>24</td>
</tr>

<tr>
    <td>7.1</td>
    <td>190</td>
    <td>153</td>
    <td>159</td>
    <td>125</td>
</tr>

<tr>
    <td>7.2</td>
    <td>133</td>
    <td>130</td>
    <td>144</td>
    <td>133</td>
</tr>

<tr>
    <td>7.3</td>
    <td>116</td>
    <td>104</td>
    <td>106</td>
    <td>56</td>
</tr>

<tr>
    <td>7.4</td>
    <td>69</td>
    <td>86</td>
    <td>98</td>
    <td>97</td>
</tr>

<tr>
    <td>8.0</td>
    <td>160</td>
    <td>94</td>
    <td>103</td>
    <td>144</td>
</tr>

<tr>
    <td>8.1</td>
    <td>-</td>
    <td>125</td>
    <td>129</td>
    <td>107</td>
</tr>

<tr>
    <td>8.2</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>94</td>
</tr>

</table>
</div>

There are two important notes to make here.

1. This tables shows the **minimum required version**. That means that packages with a minimal version of, for example, 8.0, could also support PHP 8.1 or and PHP 8.2.
2. If you count the numbers, you'll notice there are some differences between each year. Not every package lists a valid version string.

<br>

Instead of comparing absolute numbers, it's best to plot this data into a chart for a relative comparison, so that we can see changes over time:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2023-jul-03.svg)](/resources/img/blog/version-stats/2023-jul-03.svg)

<em class="center small">[Minimal PHP requirement over time](/resources/img/blog/version-stats/2023-jul-03.svg)</em>

There seems to be **a pretty big leap in PHP 8.0 and PHP 8.1 being the minimal versions**  — a good thing. After all, the open source community plays a big part in pushing the community forward by increasing their minimal required version.

---

That's all data I have to share for this edition of PHP's version stats. You can always reach me via [email](mailto:brendt@stitcher.io) if you want to share your thoughts or have questions. You can also [subscribe to my newsletter](/mail) if you want to receive updates about this blog in the future.

{{ cta:like }}

{{ cta:mail }}

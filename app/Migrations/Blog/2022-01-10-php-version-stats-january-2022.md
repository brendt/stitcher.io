It's that time again: my biyearly summary of which PHP versions are used across the community. It's been [6 months](/blog/php-version-stats-july-2021) since my previous post and during that time [PHP 8.1](/blog/new-in-php-81) was released. It'll be interesting to see some numbers on this newest version as well.

As always, it's important to note that I'm working with the data available to us. That means that these charts are no 100% accurate representation of the PHP community as a whole, but they _are_ an accurate representation of one of the most prominent parts of PHP: the [packagist ecosystem](https://packagist.org/php-statistics). 

{{ ad:carbon }}

## Usage Statistics

Let's start with the raw numbers: the percentage of PHP versions being used, today and six months ago.

<table>

<tr class="table-head">
    <td>Version</td>
    <td>July, 2021 (%)</td>
    <td>January, 2022 (%)</td>
</tr>

<tr>
    <td>8.1</td>
    <td>0.1</td>
    <td>9.1</td>
</tr>

<tr>
    <td>8.0</td>
    <td>14.7</td>
    <td>23.9</td>
</tr>

<tr>
    <td>7.4</td>
    <td>46.8</td>
    <td>43.9</td>
</tr>

<tr>
    <td>7.3</td>
    <td>19.2</td>
    <td>12.0</td>
</tr>

<tr>
    <td>7.2</td>
    <td>10.4</td>
    <td>6.6</td>
</tr>

<tr>
    <td>7.1</td>
    <td>3.8</td>
    <td>2.4</td>
</tr>

<tr>
    <td>7.0</td>
    <td>1.3</td>
    <td>0.8</td>
</tr>

</table>

Note that I've omitted all versions that didn't have more than 1% usage back in July, 2021. Visualizing this data looks something like this:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2022-jan-01.svg)](/resources/img/blog/version-stats/2022-jan-01.svg)

<em class="center small">[Evolution of version usage](/resources/img/blog/version-stats/2022-jan-01.svg)</em>

It's good to see PHP 8.1 being used in almost 10% of all composer installs, only one month after its release. It makes sense that it's easier picked up on for projects already on PHP 8.0, since it's fairly easy to upgrade from PHP 8.0 to PHP 8.1.

I'm also happy to see PHP 8.0's growth, although PHP 8.0 and 8.1 combined only account for one third of all installs. That means that two out of three composer installs use a PHP versions that [isn't actively supported](https://www.php.net/supported-versions.php) any more. 

Over the past years, I've been making it my goal to educate the people around me about the importance of keeping software up to date. I believe we — you and I — all have a responsibility to carry. If you want some hands-on tips on how to start, you can read [this post](/blog/a-storm-in-a-glass-of-water) about the why and how of managing PHP version ugrades.

> **two out of three composer installs use a PHP versions that isn't actively supported any more**

Moving on the the all-time overview chart, here you can see the evolution of version usage across time.

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2022-jan-02.svg)](/resources/img/blog/version-stats/2022-jan-02.svg)

<em class="center small">[All time evolution](/resources/img/blog/version-stats/2022-jan-02.svg)</em>

Despite PHP 7.4 starting its downward trajectory, it's clear that it still has a way to go. Let's hope we'll see a steeper decline in six months.

## Required versions

Another interesting metric is the minimum required version across packages. I've used Nikita's [popular package analyzer](*https://github.com/nikic/popular-package-analysis) to download the 1000 most popular composer packages, and wrote a little script to get the lowest version they support from their `composer.json`. Here are the results:

<table>

<tr class="table-head">
    <td>Version</td>
    <td>July, 2021 (#)</td>
    <td>January, 2022 (#)</td>
</tr>

<tr>
    <td>8.0</td>
    <td>117</td>
    <td>160</td>
</tr>

<tr>
    <td>7.4</td>
    <td>56</td>
    <td>69</td>
</tr>

<tr>
    <td>7.3</td>
    <td>133</td>
    <td>116</td>
</tr>

<tr>
    <td>7.2</td>
    <td>142</td>
    <td>133</td>
</tr>

<tr>
    <td>7.1</td>
    <td>182</td>
    <td>190</td>
</tr>

<tr>
    <td>7.0</td>
    <td>31</td>
    <td>29</td>
</tr>

<tr>
    <td>5.6</td>
    <td>61</td>
    <td>49</td>
</tr>

<tr>
    <td>5.5</td>
    <td>43</td>
    <td>42</td>
</tr>

<tr>
    <td>5.4</td>
    <td>41</td>
    <td>43</td>
</tr>

<tr>
    <td>5.3</td>
    <td>97</td>
    <td>83</td>
</tr>

<tr>
    <td>5.2</td>
    <td>12</td>
    <td>10</td>
</tr>

<tr>
    <td>5.0</td>
    <td>2</td>
    <td>2</td>
</tr>

</table>

You'll notice there are a little less than 1000 packages included, that's because some of the 1000 most popular packages don't specifically require a PHP version.

For visual learners, here's the same data visualised in a chart: 

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2022-jan-03.svg)](/resources/img/blog/version-stats/2022-jan-03.svg)

<em class="center small">[Minimal PHP requirement over time](/resources/img/blog/version-stats/2022-jan-03.svg)</em>

You might be surprised not to see PHP 8.1 here yet, but keep in mind that this data shows the _minimum_ required version. It doesn't mean that no packages support PHP 8.1, it only means they also support PHP 8.0 or lower versions. That makes a lot of sense given that PHP 8.1 has only been released for a little more than a month.

By the way, I'm talking about PHP 8.0 and 8.1 as if you're all already familiar with these newer versions. If that's not the case, or if you want 3-minute refresher about all the cool things that are happening in the PHP world these days, check out my video about modern day PHP!

<iframe width="560" height="420" src="https://www.youtube.com/embed/W3p8BGeiTwQ" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

---

So, in summary: I see similar trends as previous years, with the majority of composer installs still running outdated versions. I know many have good reasons why they can't or won't upgrade, but I also believe it has never been easier to stay up-to-date with modern PHP than it is today. Investing a couple of hours or days per year to keep your codebase healthy and up-to-date shouldn't be an issue for anyone. I hope _you_ want to help create awareness for this issue. You can start by sharing this blog post or my [recap video](https://www.youtube.com/embed/W3p8BGeiTwQ).

What are your thoughts on these stats? Are you already using [PHP 8.1](/blog/new-in-php-81)? Let me know your thoughts on [Twitter](*https://twitter.com/brendt_gd) and subscribe to [my newsletter](/newsletter/subscribe) if you want to be kept up-to-date about these posts!

{{ cta:like }}

{{ cta:mail }}

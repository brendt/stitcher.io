It's that time again: my biyearly summary of which PHP versions are used across the community. You can read the previous edition [here](/blog/php-version-stats-july-2022).

As always, it's important to note that I'm working with the data available to us. That means that these charts are not a 100% accurate representation of the PHP community as a whole, but they _are_ an accurate representation of one of the most prominent parts of PHP: the [packagist ecosystem](https://packagist.org/php-statistics).

{{ ad:carbon }}

## Usage Statistics

Let's start with the percentage of PHP versions being used today, and compare it to the previous three editions, note that I've omitted all versions that don't have more than 1% usage:

<table>

<tr class="table-head">
    <td>Version</td>
    <td>2021-07</td>
    <td>2022-01</td>
    <td>2022-07</td>
    <td>2023-01</td>
</tr>

<tr>
    <td>8.2</td>
    <td>0.0%</td>
    <td>0.0%</td>
    <td>0.0%</td>
    <td>4.7%</td>
</tr>

<tr>
    <td>8.1</td>
    <td>0.1%</td>
    <td>9.1%</td>
    <td>24.5%</td>
    <td>38.8%</td>
</tr>

<tr>
    <td>8.0</td>
    <td>14.7%</td>
    <td>23.9%</td>
    <td>20.6%</td>
    <td>16.2%</td>
</tr>

<tr>
    <td>7.4</td>
    <td>46.8%</td>
    <td>43.9%</td>
    <td>38.4%</td>
    <td>27.7%</td>
</tr>

<tr>
    <td>7.3</td>
    <td>19.2%</td>
    <td>12.0%</td>
    <td>8.0%</td>
    <td>5.3%</td>
</tr>

<tr>
    <td>7.2</td>
    <td>10.4%</td>
    <td>6.6%</td>
    <td>5.1%</td>
    <td>4.3%</td>
</tr>

<tr>
    <td>7.1</td>
    <td>3.8%</td>
    <td>2.4%</td>
    <td>1.9%</td>
    <td>1.8%</td>
</tr>

</table>

Visualizing this data looks like this:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2023-jan-01.svg)](/resources/img/blog/version-stats/2023-jan-01.svg)

<em class="center small">[Evolution of version usage](/resources/img/blog/version-stats/2023-jan-01.svg)</em>

We can see a decent growth for PHP 8.* versions, I think that's great news! It's also great to see PHP 8.0 usage already declining: PHP 8.0 went into [security fixes only mode](https://www.php.net/supported-versions.php) at the end of last year, and I hope to see its usage decline a lot more this year. Keep in mind that **PHP 8.0 will reach end of life on November 26, 2023**. So it's crucial that projects start preparing to upgrade in the coming months. 

PHP 7.4 reached end of life last year, so at the same time it's worrying that more than 25% of projects are still using it! Let's hope to see this number decline soon.

This data beautifully visualizes the division within the PHP community: one part is keeping up with modern PHP, while another one stays helplessly behind. I know there are many reasons to stay behind — often driven by business requirements and constraints — but it's crucial to realise that a lot of PHP projects are in fact running insecure and slow versions in production because of it.

{{ cta:dynamic }}

Moving on to the all-time overview chart, here you can see the evolution of version usage across time:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2023-jan-02.svg)](/resources/img/blog/version-stats/2023-jan-02.svg)

<em class="center small">[All time evolution](/resources/img/blog/version-stats/2023-jan-02.svg)</em>

Like I said: the decline of 7.4 is going too slow to my liking. Compare it to the much steeper decline of PHP 5.5 back in 2015 when PHP 7.0 was released: I would have liked to see the same happen with PHP 7.4 and have people move to PHP 8.0, but unfortunately that's not the case.

I feel like I'm repeating myself every year, but I really hope that people upgrade their projects sooner in the future. I'm curious to learn how this part of the PHP community can be helped. I feel that tools like [Rector](https://getrector.org/) to automate upgrades have so much potential, if only people started using it.

## Required versions

Next, I used Nikita's [popular package analyzer](*https://github.com/nikic/popular-package-analysis) to download the 1000 most popular composer packages. I used a little script to get their minimum required version. Here are the results:

<table>

<tr class="table-head">
    <td>Version</td>
    <td>2021-07</td>
    <td>2022-01</td>
    <td>2022-07</td>
    <td>2023-01</td>
</tr>

<tr>
    <td>8.2</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
</tr>

<tr>
    <td>8.1</td>
    <td>-</td>
    <td>-</td>
    <td>125</td>
    <td>129</td>
</tr>

<tr>
    <td>8.0</td>
    <td>117</td>
    <td>160</td>
    <td>94</td>
    <td>103</td>
</tr>

<tr>
    <td>7.4</td>
    <td>56</td>
    <td>69</td>
    <td>86</td>
    <td>98</td>
</tr>

<tr>
    <td>7.3</td>
    <td>133</td>
    <td>116</td>
    <td>104</td>
    <td>106</td>
</tr>

<tr>
    <td>7.2</td>
    <td>142</td>
    <td>133</td>
    <td>130</td>
    <td>144</td>
</tr>

<tr>
    <td>7.1</td>
    <td>182</td>
    <td>190</td>
    <td>153</td>
    <td>159</td>
</tr>

<tr>
    <td>7.0</td>
    <td>31</td>
    <td>29</td>
    <td>29</td>
    <td>30</td>
</tr>

<tr>
    <td>5.6</td>
    <td>61</td>
    <td>49</td>
    <td>42</td>
    <td>43</td>
</tr>

<tr>
    <td>5.5</td>
    <td>43</td>
    <td>42</td>
    <td>35</td>
    <td>37</td>
</tr>

<tr>
    <td>5.4</td>
    <td>41</td>
    <td>43</td>
    <td>40</td>
    <td>40</td>
</tr>

<tr>
    <td>5.3</td>
    <td>97</td>
    <td>83</td>
    <td>77</td>
    <td>78</td>
</tr>

<tr>
    <td>5.2</td>
    <td>12</td>
    <td>10</td>
    <td>10</td>
    <td>10</td>
</tr>

</table>

There are two important notes to make here.

1. This tables shows the **minimum required version**. It makes sense that none of the 1000 packages already supports PHP 8.2, since it's been only here for a month.
2. If you count the numbers, you'll notice there are some differences between each year. Not every package lists a version, so not all 1000 packages can be parsed.

<br>

Instead of comparing absolute numbers, it's best to plot this data into a chart for a relative comparison, so that we can see changes over time: 

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2023-jan-03.svg)](/resources/img/blog/version-stats/2023-jan-03.svg)

<em class="center small">[Minimal PHP requirement over time](/resources/img/blog/version-stats/2023-jan-03.svg)</em>

You can see there's a _slight_ increase in PHP 7.* requirements. It's a good evolution, but still very slow compared to how fast PHP is moving forward.

In my opinion, package authors should push more to require only supported PHP versions. I think it's the only way for PHP to keep moving forward at a decent rate, and for the community to keep up. On top of that: yearly upgrades are much easier to do than to stay on, for example, PHP 7.4 and try to make the jump directly to PHP 8.2.

<p>
<iframe width="560" height="422" src="https://www.youtube.com/embed/z0Tzb6SVwr4" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
</p>

---

In closing, if you take one thing away from this post, I hope it's that it's time to upgrade to at least PHP 8.1, preferably PHP 8.2. It's not as difficult as you might think, and it's [definitely worth your time](/blog/a-storm-in-a-glass-of-water).

What are your thoughts on these stats? Are you using [PHP 8.2](/blog/new-in-php-82)? Let me know your thoughts on [Twitter](*https://twitter.com/brendt_gd) and subscribe to [my newsletter](/newsletter/subscribe) if you want to be kept up-to-date about these posts!

{{ cta:like }}

{{ cta:mail }}

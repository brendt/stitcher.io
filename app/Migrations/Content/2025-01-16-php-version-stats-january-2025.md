Happy new year! Twice a year, I write an update on PHP version usage across the community. You can read the previous edition [here](/blog/php-version-stats-july-2024), but I'll also include historic data in this post.

As always, I'm working with the data available: these charts are not a 100% accurate representation of the PHP community as a whole, but they _are_ an accurate representation of one of the most prominent parts of PHP: the [packagist ecosystem](https://packagist.org/php-statistics).

Let's see what has changed over the past six months, and also check out how PHP 8.4 is doing, a month after its release.

{{ cta:packagist }}

## Usage Statistics

Let's start by looking at the percentage of PHP versions being used today. I've omitted all versions that don't have more than 1% usage:

<div class="table-container">
<table>

<tr class="table-head">
    <td>Version</td>
    <td>2023-07</td>
    <td>2024-01</td>
    <td>2024-07</td>
    <td>2025-01</td>
</tr>

<tr>
    <td>7.2</td>
    <td>4.3%</td>
    <td>2.5%</td>
    <td>2.0%</td>
    <td>1.6%</td>
</tr>

<tr>
    <td>7.3</td>
    <td>4.2%</td>
    <td>3.2%</td>
    <td>1.9%</td>
    <td>1.5%</td>
</tr>

<tr>
    <td>7.4</td>
    <td>19.9%</td>
    <td>13.6%</td>
    <td>10.2%</td>
    <td>7.6%</td>
</tr>

<tr>
    <td>8.0</td>
    <td>12.3%</td>
    <td>7.2%</td>
    <td>5.4%</td>
    <td>3.4%</td>
</tr>

<tr>
    <td>8.1</td>
    <td>39.3%</td>
    <td>35.2%</td>
    <td>26.1%</td>
    <td>18.1%</td>
</tr>

<tr>
    <td>8.2</td>
    <td>17.2%</td>
    <td>29.4%</td>
    <td>32.3%</td>
    <td>28.6%</td>
</tr>

<tr>
    <td>8.3</td>
    <td>0.2%</td>
    <td>6.4%</td>
    <td>19.9%</td>
    <td>32.7%</td>
</tr>

<tr>
    <td>8.4</td>
    <td>0.0%</td>
    <td>0.0%</td>
    <td>0.0%</td>
    <td>5.1%</td>
</tr>

</table>
</div>

Visualizing this data looks like this:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2025-jan-01.svg)](/resources/img/blog/version-stats/2025-jan-01.svg)

<em class="center small">[Evolution of version usage](/resources/img/blog/version-stats/2025-jan-01.svg)</em>

Ever since PHP 8.1, I've begun making these posts in January and July. I've deliberately chosen those months because by January, the newest PHP release has been around one month old, and I think it's interesting to how many early adopters there are. So let's compare first-month usage since PHP 8.1 as well:

<div class="table-container">
<table>

<tr class="table-head">
    <td>Version</td>
    <td>First-month usage</td>
</tr>

<tr>
    <td>8.1</td>
    <td>9.1%</td>
</tr>

<tr>
    <td>8.2</td>
    <td>4.7%</td>
</tr>

<tr>
    <td>8.3</td>
    <td>6.4%</td>
</tr>

<tr>
    <td>8.4</td>
    <td>5.1%</td>
</tr>

</table>
</div>

PHP 8.4 isn't performing the best, but also not the worst. It makes sense that a *.1 release sees faster adoption, as it usually irons out some of the kinks of the previous *.0 release. I'm a little surprised with 8.3 taking the second place: it was a rather boring release, while 8.4 has much more exciting new features. On the other hand: boring might exactly be the reason why people are able to update more quickly. 

Finally, let's bundle all data together in one big chart that starts with PHP 5.3 in 2013:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2025-jan-02.svg)](/resources/img/blog/version-stats/2025-jan-02.svg)

<em class="center small">[All time evolution](/resources/img/blog/version-stats/2025-jan-02.svg)</em>

## Required versions

Besides Packagist's data, I also use Nikita's [popular package analyzer](*https://github.com/nikic/popular-package-analysis) to download the 1000 most popular composer packages. I use a script to scans these packages and determine their minimum required version. Here are the results:

<div class="table-container">
<table>

<tr class="table-head">
    <td>Version</td>
    <td>2023-07</td>
    <td>2024-01</td>
    <td>2024-07</td>
    <td>2025-01</td>
</tr>

<tr>
    <td>5.2</td>
    <td>7</td>
    <td>7</td>
    <td>5</td>
    <td>5</td>
</tr>

<tr>
    <td>5.3</td>
    <td>65</td>
    <td>58</td>
    <td>50</td>
    <td>52</td>
</tr>

<tr>
    <td>5.4</td>
    <td>31</td>
    <td>28</td>
    <td>26</td>
    <td>26</td>
</tr>

<tr>
    <td>5.5</td>
    <td>21</td>
    <td>16</td>
    <td>15</td>
    <td>15</td>
</tr>

<tr>
    <td>5.6</td>
    <td>32</td>
    <td>30</td>
    <td>29</td>
    <td>31</td>
</tr>

<tr>
    <td>7.0</td>
    <td>24</td>
    <td>24</td>
    <td>24</td>
    <td>25</td>
</tr>

<tr>
    <td>7.1</td>
    <td>125</td>
    <td>100</td>
    <td>93</td>
    <td>101</td>
</tr>

<tr>
    <td>7.2</td>
    <td>133</td>
    <td>123</td>
    <td>118</td>
    <td>123</td>
</tr>

<tr>
    <td>7.3</td>
    <td>56</td>
    <td>49</td>
    <td>42</td>
    <td>45</td>
</tr>

<tr>
    <td>7.4</td>
    <td>97</td>
    <td>87</td>
    <td>80</td>
    <td>81</td>
</tr>

<tr>
    <td>8.0</td>
    <td>144</td>
    <td>126</td>
    <td>123</td>
    <td>128</td>
</tr>

<tr>
    <td>8.1</td>
    <td>107</td>
    <td>154</td>
    <td>184</td>
    <td>194</td>
</tr>

<tr>
    <td>8.2</td>
    <td>94</td>
    <td>135</td>
    <td>153</td>
    <td>171</td>
</tr>

<tr>
    <td>8.3</td>
    <td>-</td>
    <td>0</td>
    <td>4</td>
    <td>4</td>
</tr>

<tr>
    <td>8.4</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>0</td>
</tr>

</table>
</div>

There are two important notes to make here.

1. This tables shows the **minimum required version**. That means that packages with a minimal version of, for example, 8.0, could also support PHP 8.1, PHP 8.2, PHP 8.3, and PHP 8.4.
2. If you count the numbers, you'll notice there are some differences between each year. Not every package lists a valid version string.

{{ cta:packagist }}

It's easiest to visualize this data into a chart for a relative comparison, so that we can see changes over time:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2025-jan-03.svg)](/resources/img/blog/version-stats/2025-jan-03.svg)

<em class="center small">[Minimal PHP requirement over time](/resources/img/blog/version-stats/2025-jan-03.svg)</em>

It seems that adoption rate of newer versions have slowed down, though remember that this isn't necessarily a bad thing: packages that require `php: ^8.0` will automatically support PHP 8.4 as well. 

On the other hand (I've made this argument many times before): I believe the PHP community would benefit overall if open source maintainers pushed more towards keeping up-to-date with the newest PHP versions. We're at a time when upgrades have never been easier thanks to automation tools like Rector; and keeping projects up-to-date with the latest PHP version only has benefits (security, performance, and newer features).

I, for one, am very excited about PHP 8.4. It's one of the most [impactful releases in recent PHP history](/blog/new-in-php-84), and I really enjoy using it! 

{{ cta:like }}

{{ cta:mail }}

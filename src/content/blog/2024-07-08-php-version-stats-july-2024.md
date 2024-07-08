Every six months, I do an update on which PHP versions are used across the community. You can read the previous edition [here](/blog/php-version-stats-january-2024), I'll also include historic data in this post.

Keep in mind note that I'm working with the data available. That means that these charts are not a 100% accurate representation of the PHP community as a whole, but they _are_ an accurate representation of one of the most prominent parts of PHP: the [packagist ecosystem](https://packagist.org/php-statistics).

{{ cta:packagist }}

## Usage Statistics

As usual, we start by looking at the percentage of PHP versions being used today, note that I've omitted all versions that don't have more than 1% usage:

<div class="table-container">
<table>

<tr class="table-head">
    <td>Version</td>
    <td>2023-01</td>
    <td>2023-07</td>
    <td>2024-01</td>
    <td>2024-07</td>
</tr>

<tr>
    <td>7.2</td>
    <td>4.3%</td>
    <td>4.3%</td>
    <td>2.5%</td>
    <td>2.0%</td>
</tr>

<tr>
    <td>7.3</td>
    <td>5.3%</td>
    <td>4.2%</td>
    <td>3.2%</td>
    <td>1.9%</td>
</tr>

<tr>
    <td>7.4</td>
    <td>27.7%</td>
    <td>19.9%</td>
    <td>13.6%</td>
    <td>10.2%</td>
</tr>

<tr>
    <td>8.0</td>
    <td>16.2%</td>
    <td>12.3%</td>
    <td>7.2%</td>
    <td>5.4%</td>
</tr>

<tr>
    <td>8.1</td>
    <td>38.8%</td>
    <td>39.3%</td>
    <td>35.2%</td>
    <td>26.1%</td>
</tr>

<tr>
    <td>8.2</td>
    <td>4.7%</td>
    <td>17.2%</td>
    <td>29.4%</td>
    <td>32.3%</td>
</tr>

<tr>
    <td>8.3</td>
    <td>0.0%</td>
    <td>0.2%</td>
    <td>6.4%</td>
    <td>19.9%</td>
</tr>

</table>
</div>

Visualizing this data looks like this:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2024-jul-01.svg)](/resources/img/blog/version-stats/2024-jul-01.svg)

<em class="center small">[Evolution of version usage](/resources/img/blog/version-stats/2024-jul-01.svg)</em>

An additional data point I wanted to look into this time, is to compare the growth of each PHP version the first half year after its release. 

<div class="table-container">
<table>

<tr class="table-head">
    <td>Version</td>
    <td>6 month adoption rate</td>
</tr>

<tr>
    <td>7.0</td>
    <td>19.0%</td>
</tr>

<tr>
    <td>7.1</td>
    <td>16.2%</td>
</tr>

<tr>
    <td>7.2</td>
    <td>12.4%</td>
</tr>

<tr>
    <td>7.3</td>
    <td>19.8%</td>
</tr>

<tr>
    <td>7.4</td>
    <td>17.1%</td>
</tr>

<tr>
    <td>8.0</td>
    <td>9.2%</td>
</tr>

<tr>
    <td>8.1</td>
    <td>15.4%</td>
</tr>

<tr>
    <td>8.2</td>
    <td>12.5%</td>
</tr>

<tr>
    <td>8.3</td>
    <td>13.5%</td>
</tr>

</table>
</div>

What's interesting is that the PHP 7.* versions seem to have had a faster adoption rate compared to PHP 8.* releases. From a personal point of view, I also feel less need to immediately update to newer PHP versions, especially since they didn't offer that many exciting features the past two years. I wonder if the adoption rate for PHP 8.4 will be higher or lower, especially since it has some very nice features (like [property hooks](/blog/new-in-php-84#property-hooks-rfc)). 


Let's take one more look at version evolution across time, you can spot the slowed down adoption rate in this chart as well::

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2024-jul-02.svg)](/resources/img/blog/version-stats/2024-jul-02.svg)

<em class="center small">[All time evolution](/resources/img/blog/version-stats/2024-jul-02.svg)</em>

## Required versions

Next, I used Nikita's [popular package analyzer](*https://github.com/nikic/popular-package-analysis) to download the 1000 most popular composer packages. I use a script that scans these packages to determine their minimum required version. Here are the results:

<div class="table-container">
<table>

<tr class="table-head">
    <td>Version</td>
    <td>2023-01</td>
    <td>2023-07</td>
    <td>2024-01</td>
    <td>2024-07</td>
</tr>

<tr>
    <td>5.2</td>
    <td>10</td>
    <td>7</td>
    <td>7</td>
    <td>5</td>
</tr>

<tr>
    <td>5.3</td>
    <td>78</td>
    <td>65</td>
    <td>58</td>
    <td>50</td>
</tr>

<tr>
    <td>5.4</td>
    <td>40</td>
    <td>31</td>
    <td>28</td>
    <td>26</td>
</tr>

<tr>
    <td>5.5</td>
    <td>37</td>
    <td>21</td>
    <td>16</td>
    <td>15</td>
</tr>

<tr>
    <td>5.6</td>
    <td>43</td>
    <td>32</td>
    <td>30</td>
    <td>29</td>
</tr>

<tr>
    <td>7.0</td>
    <td>30</td>
    <td>24</td>
    <td>24</td>
    <td>24</td>
</tr>

<tr>
    <td>7.1</td>
    <td>159</td>
    <td>125</td>
    <td>100</td>
    <td>93</td>
</tr>

<tr>
    <td>7.2</td>
    <td>144</td>
    <td>133</td>
    <td>123</td>
    <td>118</td>
</tr>

<tr>
    <td>7.3</td>
    <td>106</td>
    <td>56</td>
    <td>49</td>
    <td>42</td>
</tr>

<tr>
    <td>7.4</td>
    <td>98</td>
    <td>97</td>
    <td>87</td>
    <td>80</td>
</tr>

<tr>
    <td>8.0</td>
    <td>103</td>
    <td>144</td>
    <td>126</td>
    <td>123</td>
</tr>

<tr>
    <td>8.1</td>
    <td>129</td>
    <td>107</td>
    <td>154</td>
    <td>184</td>
</tr>

<tr>
    <td>8.2</td>
    <td>-</td>
    <td>94</td>
    <td>135</td>
    <td>153</td>
</tr>

<tr>
    <td>8.3</td>
    <td>-</td>
    <td>-</td>
    <td>0</td>
    <td>4</td>
</tr>

</table>
</div>

There are two important notes to make here.

1. This tables shows the **minimum required version**. That means that packages with a minimal version of, for example, 8.0, could also support PHP 8.1, PHP 8.2, and PHP 8.3.
2. If you count the numbers, you'll notice there are some differences between each year. Not every package lists a valid version string.

{{ cta:packagist }}

Instead of comparing absolute numbers, it's best to plot this data into a chart for a relative comparison, so that we can see changes over time:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2024-jul-03.svg)](/resources/img/blog/version-stats/2024-jul-03.svg)

<em class="center small">[Minimal PHP requirement over time](/resources/img/blog/version-stats/2024-jul-03.svg)</em>

We see the first four packages requiring PHP 8.3 as their minimal version this month, however there is a huge difference compared to PHP 8.2 and PHP 8.1, which had 94 and 125 packages using them as their minimum version respectively. Granted, PHP 8.3 has been a rather boring release, with quite a lot of deprecations as well, but I didn't expect the difference to be so big. We'll see how and if this trend continues in the next year with PHP 8.4.

Once again, I'd like to remind open source authors about the responsibility we collectively hold to move the PHP ecosystem forward. Bumping minimum requirements is a good thing to do, and should — in my opinion — but done more and faster. Feel free to disagree and share your thoughts via [email](mailto:brendt@stitcher.io) or in the comments below this post.

{{ cta:like }}

{{ cta:mail }}

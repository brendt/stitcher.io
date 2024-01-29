It's time for another version stats post! This is my biyearly summary of which PHP versions are used across the community. You can read the previous edition [here](/blog/php-version-stats-july-2024), but I'll also include historic data in this post.

Keep in mind note that I'm working with the data available. That means that these charts are not a 100% accurate representation of the PHP community as a whole, but they _are_ an accurate representation of one of the most prominent parts of PHP: the [packagist ecosystem](https://packagist.org/php-statistics).

{{ cta:packagist }}

## Usage Statistics

Let's start with the percentage of PHP versions being used today, and compare it to the previous three editions, note that I've omitted all versions that don't have more than 1% usage:

<div class="table-container">
<table>

<tr class="table-head">
    <td>Version</td>
    <td>2022-07</td>
    <td>2023-01</td>
    <td>2023-07</td>
    <td>2024-01</td>
</tr>


<tr>
    <td>7.1</td>
    <td>1.9%</td>
    <td>1.8%</td>
    <td>1.3%</td>
    <td>1.0%</td>
</tr>

<tr>
    <td>7.2</td>
    <td>5.1%</td>
    <td>4.3%</td>
    <td>4.3%</td>
    <td>2.5%</td>
</tr>

<tr>
    <td>7.3</td>
    <td>8.0%</td>
    <td>5.3%</td>
    <td>4.2%</td>
    <td>3.2%</td>
</tr>

<tr>
    <td>7.4</td>
    <td>38.4%</td>
    <td>27.7%</td>
    <td>19.9%</td>
    <td>13.6%</td>
</tr>

<tr>
    <td>8.0</td>
    <td>20.6%</td>
    <td>16.2%</td>
    <td>12.3%</td>
    <td>7.2%</td>
</tr>

<tr>
    <td>8.1</td>
    <td>24.5%</td>
    <td>38.8%</td>
    <td>39.3%</td>
    <td>35.2%</td>
</tr>

<tr>
    <td>8.2</td>
    <td>0.0%</td>
    <td>4.7%</td>
    <td>17.2%</td>
    <td>29.4%</td>
</tr>

<tr>
    <td>8.3</td>
    <td>0.0%</td>
    <td>0.0%</td>
    <td>0.2%</td>
    <td>6.4%</td>
</tr>

</table>
</div>

Visualizing this data looks like this:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2024-jan-01.svg)](/resources/img/blog/version-stats/2024-jan-01.svg)

<em class="center small">[Evolution of version usage](/resources/img/blog/version-stats/2024-jan-01.svg)</em>

There seems to be a slightly faster adoption of PHP 8.3 compared to PHP 8.2: 6.4% of projects are using PHP 8.3 within the first two months of its release, for PHP 8.2 it  was 4.7%. 

Furthermore, the PHP 7.* share continues to shrink — a good thing given that support for the 7.* series ended more than a year ago. Right now PHP 8.1 is the oldest supported version, only receiving security updates until November 25 this year. I can't help it, I keep saying the same thing over and over again, [it's important update your PHP installations](/blog/a-storm-in-a-glass-of-water)!


Moving on to the all-time overview chart, here you can see the evolution of version usage across time:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2024-jan-02.svg)](/resources/img/blog/version-stats/2024-jan-02.svg)

<em class="center small">[All time evolution](/resources/img/blog/version-stats/2024-jan-02.svg)</em>

{{ cta:dynamic }}

## Required versions

Next, I used Nikita's [popular package analyzer](*https://github.com/nikic/popular-package-analysis) to download the 1000 most popular composer packages. I use a script that scans these packages to determine their minimum required version. Here are the results:

<div class="table-container">
<table>

<tr class="table-head">
    <td>Version</td>
    <td>2022-07</td>
    <td>2023-01</td>
    <td>2023-07</td>
    <td>2024-01</td>
</tr>

<tr>
    <td>5.2</td>
    <td>10</td>
    <td>10</td>
    <td>7</td>
    <td>7</td>
</tr>

<tr>
    <td>5.3</td>
    <td>77</td>
    <td>78</td>
    <td>65</td>
    <td>58</td>
</tr>

<tr>
    <td>5.4</td>
    <td>40</td>
    <td>40</td>
    <td>31</td>
    <td>28</td>
</tr>

<tr>
    <td>5.5</td>
    <td>35</td>
    <td>37</td>
    <td>21</td>
    <td>16</td>
</tr>

<tr>
    <td>5.6</td>
    <td>42</td>
    <td>43</td>
    <td>32</td>
    <td>30</td>
</tr>

<tr>
    <td>7.0</td>
    <td>29</td>
    <td>30</td>
    <td>24</td>
    <td>24</td>
</tr>

<tr>
    <td>7.1</td>
    <td>153</td>
    <td>159</td>
    <td>125</td>
    <td>100</td>
</tr>

<tr>
    <td>7.2</td>
    <td>130</td>
    <td>144</td>
    <td>133</td>
    <td>123</td>
</tr>

<tr>
    <td>7.3</td>
    <td>104</td>
    <td>106</td>
    <td>56</td>
    <td>49</td>
</tr>

<tr>
    <td>7.4</td>
    <td>86</td>
    <td>98</td>
    <td>97</td>
    <td>87</td>
</tr>

<tr>
    <td>8.0</td>
    <td>94</td>
    <td>103</td>
    <td>144</td>
    <td>126</td>
</tr>

<tr>
    <td>8.1</td>
    <td>125</td>
    <td>129</td>
    <td>107</td>
    <td>154</td>
</tr>

<tr>
    <td>8.2</td>
    <td>-</td>
    <td>-</td>
    <td>94</td>
    <td>135</td>
</tr>

<tr>
    <td>8.3</td>
    <td>-</td>
    <td>-</td>
    <td>-</td>
    <td>0</td>
</tr>

</table>
</div>

There are two important notes to make here.

1. This tables shows the **minimum required version**. That means that packages with a minimal version of, for example, 8.0, could also support PHP 8.1, PHP 8.2, and PHP 8.3.
2. If you count the numbers, you'll notice there are some differences between each year. Not every package lists a valid version string.

<br>

Instead of comparing absolute numbers, it's best to plot this data into a chart for a relative comparison, so that we can see changes over time:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2024-jan-03.svg)](/resources/img/blog/version-stats/2024-jan-03.svg)

<em class="center small">[Minimal PHP requirement over time](/resources/img/blog/version-stats/2024-jan-03.svg)</em>

Talking about progression, I'd like to remind open source maintainers about the power and responsibility they hold. Image if all modern open source packages only supported PHP versions that were actively worked on. My suspicion is that many more projects would be encouraged to update faster; eventually leading to a healthier, more performant, and more secure ecosystem. Open source maintainers yield quite a lot of power in this regard.

Also keep in mind that forcing a new minimal PHP requirement doesn't automatically block older projects from using your code: outdated projects can still download older versions of your packages, so there's really no good argument not to do it from a package maintainer's point of view.

{{ cta:packagist }}

That's all data I have to share for this edition of PHP's version stats. You can always reach me via [email](mailto:brendt@stitcher.io) if you want to share your thoughts or have questions.

{{ cta:like }}

{{ cta:mail }}

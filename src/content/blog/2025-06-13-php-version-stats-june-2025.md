Welcome back to another blog post about PHP's version usage across the community. You can read the previous edition [here](/blog/php-version-stats-january-2025), but I'll also include historic data in this post.

These posts always start with a disclaimer: I'm working with the data available. So these charts aren't a 100% accurate representation of the PHP community as a whole, but they _are_ an accurate representation of one of the most prominent parts of PHP: the [packagist ecosystem](https://packagist.org/php-statistics).

Let's see what has changed the last half-year, and also check out how PHP 8.4 is being adopted half a year after its release.

{{ cta:packagist }}

## Usage Statistics

Let's start by looking at the percentage of PHP versions being used today. I've omitted all versions that don't have more than 1% usage:

<div class="table-container">
<table>

<tr class="table-head">
    <td>Version</td>
    <td>2024-01</td>
    <td>2024-07</td>
    <td>2025-01</td>
    <td>2025-06</td>
</tr>

<tr>
    <td>7.2</td>
    <td>2.5%</td>
    <td>2.0%</td>
    <td>1.6%</td>
    <td>1.6%</td>
</tr>

<tr>
    <td>7.3</td>
    <td>3.2%</td>
    <td>1.9%</td>
    <td>1.5%</td>
    <td>1.4%</td>
</tr>

<tr>
    <td>7.4</td>
    <td>13.6%</td>
    <td>10.2%</td>
    <td>7.6%</td>
    <td>6.9%</td>
</tr>

<tr>
    <td>8.0</td>
    <td>7.2%</td>
    <td>5.4%</td>
    <td>3.4%</td>
    <td>3.3%</td>
</tr>

<tr>
    <td>8.1</td>
    <td>35.2%</td>
    <td>26.1%</td>
    <td>18.1%</td>
    <td>13.4%</td>
</tr>

<tr>
    <td>8.2</td>
    <td>29.4%</td>
    <td>32.3%</td>
    <td>28.6%</td>
    <td>24.8%</td>
</tr>

<tr>
    <td>8.3</td>
    <td>6.4%</td>
    <td>19.9%</td>
    <td>32.7%</td>
    <td>34.0%</td>
</tr>

<tr>
    <td>8.4</td>
    <td>0.0%</td>
    <td>0.0%</td>
    <td>5.1%</td>
    <td>13.7%</td>
</tr>

</table>
</div>

Visualizing this data looks like this:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2025-jun-01.svg)](/resources/img/blog/version-stats/2025-jun-01.svg)

<em class="center small">[Evolution of version usage](/resources/img/blog/version-stats/2025-jun-01.svg)</em>

What stands out is that PHP 8.4 has a slightly worse adoption rate compared to previous PHP releases: 13.6% after half a year:

<div class="table-container">
<table>

<tr class="table-head">
    <td>Version</td>
    <td>Usage after 6 months</td>
</tr>

<tr>
    <td>8.0</td>
    <td>15.0%</td>
</tr>

<tr>
    <td>8.1</td>
    <td>21.6%</td>
</tr>

<tr>
    <td>8.2</td>
    <td>15.6%</td>
</tr>

<tr>
    <td>8.3</td>
    <td>16.7%</td>
</tr>

<tr>
    <td>8.4</td>
    <td>13.7%</td>
</tr>

</table>
</div>

I've been thinking about why PHP 8.4 has slower adoption compared to previous releases. After all, [PHP 8.4](/blog/new-in-php-84) is a pretty exciting release with features like property hooks, `new` without parenthesis, and asymmetric visibility. Maybe that's actually the reason why less people are upgrading to PHP 8.4: too many new and shiny things might feel overwhelming.

Although, I don't think that's the whole of the story. When I discussed this question [during a livestream](https://www.youtube.com/watch?v=iVaSGD2fCXU), people mentioned that the lack of QA tooling support is blocking them from updating to PHP 8.4. Indeed, PHP CS Fixer, for example, is [still working on PHP 8.4 support](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/milestone/173), and that's going pretty slow. I also remember PHPStan taking a while before PHP 8.4 support was added. 

Then, there are open source packages that might factor in as well, although we'll discuss those separately further down this post. First, let's combine all the historical data into one big graph visualizing PHP's usage evolution over time:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2025-jun-02.svg)](/resources/img/blog/version-stats/2025-jun-02.svg)

<em class="center small">[All time evolution](/resources/img/blog/version-stats/2025-jun-02.svg)</em>


{{ cta:packagist }}

## Required versions

An interesting data point is to analyse the *minimal required version* of the 1000 most popular packages on Packagist. This data gives a good indication of how much PHP's open source community is pushing projects forwards. There are a lot of things to say about these numbers, which I did in [a video](https://www.youtube.com/watch?v=Z4b5gqKSZmA), check it out if you'd like! 

An important note is that I've switched from using Nikita's popular package analyser to Adrian's new [packagist analyser](https://github.com/nuernbergerA/packagist-stats). This tool is much faster and saves me a lot of time. There might be some subtle differences in version parsing, though (ie. the script I hacked on top of Nikita's analyser might have had a bug or two). So I reckon that some differences here are due to that change. However, Adrian's analyser uses composer's version parser, so we can be pretty certain the new numbers are the most accurate ones.

Two other things to mention are these:

1. This tables shows the **minimum required version**. That means that packages with a minimal version of, for example, 8.0, could also support PHP 8.1, PHP 8.2, PHP 8.3, and PHP 8.4.
2. If you count the numbers, you'll notice there are some differences between each year. Not every package lists a valid version string.

<div class="table-container">
<table>

<tr class="table-head">
    <td>Version</td>
    <td>2024-01</td>
    <td>2024-07</td>
    <td>2025-01</td>
    <td>2025-06</td>
</tr>

<tr>
    <td>5.3</td>
    <td>58</td>
    <td>50</td>
    <td>52</td>
    <td>28</td>
</tr>

<tr>
    <td>5.4</td>
    <td>28</td>
    <td>26</td>
    <td>26</td>
    <td>39</td>
</tr>

<tr>
    <td>5.5</td>
    <td>16</td>
    <td>15</td>
    <td>15</td>
    <td>7</td>
</tr>

<tr>
    <td>5.6</td>
    <td>30</td>
    <td>29</td>
    <td>31</td>
    <td>18</td>
</tr>

<tr>
    <td>7.0</td>
    <td>24</td>
    <td>24</td>
    <td>25</td>
    <td>27</td>
</tr>

<tr>
    <td>7.1</td>
    <td>100</td>
    <td>93</td>
    <td>101</td>
    <td>71</td>
</tr>

<tr>
    <td>7.2</td>
    <td>123</td>
    <td>118</td>
    <td>123</td>
    <td>87</td>
</tr>

<tr>
    <td>7.3</td>
    <td>49</td>
    <td>42</td>
    <td>45</td>
    <td>59</td>
</tr>

<tr>
    <td>7.4</td>
    <td>87</td>
    <td>80</td>
    <td>81</td>
    <td>95</td>
</tr>

<tr>
    <td>8.0</td>
    <td>126</td>
    <td>123</td>
    <td>128</td>
    <td>106</td>
</tr>

<tr>
    <td>8.1</td>
    <td>154</td>
    <td>184</td>
    <td>194</td>
    <td>234</td>
</tr>

<tr>
    <td>8.2</td>
    <td>135</td>
    <td>153</td>
    <td>171</td>
    <td>187</td>
</tr>

<tr>
    <td>8.3</td>
    <td>0</td>
    <td>4</td>
    <td>4</td>
    <td>26</td>
</tr>

<tr>
    <td>8.4</td>
    <td>-</td>
    <td>-</td>
    <td>0</td>
    <td>0</td>
</tr>

</table>
</div>

It's easiest to visualize this data into a chart for a relative comparison, so that we can see changes over time:

<div class="image-noborder image-wide"></div>

[![](/resources/img/blog/version-stats/2025-jun-03.svg)](/resources/img/blog/version-stats/2025-jun-03.svg)

<em class="center small">[Minimal PHP requirement over time](/resources/img/blog/version-stats/2025-jun-03.svg)</em>

My conclusions from this graph are still the same, though: more than 50% of top PHP packages support PHP versions that are completely outdated without security releases. I think the PHP community as a whole would really benefit if packages more aggressively pushed towards only using supported PHP versions. 

It's a tricky topic, and it's what I discussed in depth in [that video](https://www.youtube.com/watch?v=Z4b5gqKSZmA) I mentioned earlier, so definitely check it out if you want to:

<iframe width="560" height="347" src="https://www.youtube.com/embed/Z4b5gqKSZmA" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

That was this time's look at the stats. I'm always eager to hear from you, so let me know your thoughts either via [mail](mailto:brendt@stitcher.io) or [Discord](https://tempestphp.com/discord).

{{ cta:mail }}

---
title: 'PHP version stats: July, 2026'
disableAds: true
meta:
    description: 'PHP usage in July, 2026'
---

Welcome back to another blog post about PHP's version usage across the community. You can read the previous edition [here](/blog/php-version-stats-june-2025), but as always I'll include historic data in this post as well. You'll notice that we skipped the edition of January 2026; I was too busy with other things at that point and didn't find the time to work on it. I did include the January 2026 data where possible in this post, for completeness. 

If this is your first time reading one of these overviews, it's important to note that I'm working with the data available. These charts aren't a 100% accurate representation of the PHP community as a whole, but they _are_ an accurate representation of one of the most prominent parts of PHP: the [packagist ecosystem](https://packagist.org/php-statistics).

Let's see how PHP evolved in 2026 and also check out how PHP 8.5 is being adopted half a year after its release!

{{ cta:packagist }}

## Usage Statistics

For the first time, I'll be using live-rendered charts in these blog posts. If you run into any issues, [let me know](mailto:brendt@stitcher.io).

We start with the most recent PHP versions, showing their usage evolution over two years. This table shows how quickly new PHP versions get adopted.

{{ version_stats_table version:2026-07-01 }}

Zooming out a little more, here's version adoption since 2021:

{{ version_stats_usage_graph version:2026-07-01 }}

What's most interesting is comparing how quickly new versions get adopted in their first year:

<div class="table-container">
    <table>
        <tr class="table-head">
            <td class="text-left">Version</td>
            <td class="text-left">Usage after 6 months</td>
        </tr>

        <tr>
            <td class="text-left">8.5</td>
            <td class="text-left">16%</td>
        </tr>

        <tr>
            <td class="text-left">8.4</td>
            <td class="text-left">14%</td>
        </tr>

        <tr>
            <td class="text-left">8.3</td>
            <td class="text-left">20%</td>
        </tr>

        <tr>
            <td class="text-left">8.2</td>
            <td class="text-left">17%</td>
        </tr>

        <tr>
            <td class="text-left">8.1</td>
            <td class="text-left">25%</td>
        </tr>

        <tr>
            <td class="text-left">8.0</td>
            <td class="text-left">15%</td>
        </tr>
    </table>
</div>

You can see how 8.5 adoption is relatively low compared to 8.1 or 8.3. Let's visualize all data in one chart to get a global overview of how PHP adoption evolves over time:

{{ version_stats_graph version:2026-07-01 }}

{{ cta:packagist }}

## Required versions

The PHP ecosystem heavily drives version adoption: the more third party packages push towards higher version requirements, the more projects are forced to stay up to date. If you want some more insights into my train of thought, you can watch me explain it best in this video:

{{ yt:Z4b5gqKSZmA }}

So that's why I also look at the *minimal required version* of the 1000 most popular packages on Packagist. This data gives a good indication of how much PHP's open source community is pushing projects forwards.

A couple of important things to mention, though:

1. This table shows the minimum required version. That means that packages with a minimal version of, for example, 8.0 could also support PHP 8.1 through PHP 8.5.
2. If you count the numbers, you'll notice there are some differences between each year. Not every package lists a valid version string. Furthermore, I switched to a more accurate parser a year ago, which might cause some slight differences.
3. Finally, remember that January 2026 was skipped.

{{ package_stats_table version:2026-07-01 }}

It's easiest to visualize this data into a chart for a relative comparison, so that we can see changes over time:

{{ package_stats_usage_graph version:2026-07-01 }}

It's good to see 111 packages jump to 8.4 at the minimum. My general conclusion from this graph is still the same, though: anything under PHP 8.2 receives no updates at all anymore. **Only 352 out of 1000 packages require a minimum PHP that's still receives security updates.** Between PHP 8.2 and PHP 8.5 though, only the latest two versions get active bugfixing support, PHP 8.2 and PHP 8.3 only get security updates; which means that **only 113 out of 1000 packages require a version that's actively supported**.

I'll end this blog post with a call to action for open source maintainers: help keep PHP up to date by pushing minimum versions to at least security supported ones. You have the power to move PHP forward.

{{ cta:mail }}

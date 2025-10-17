---
title: 'PHP 8.1: real-life performance benchmarks'
footnotes:
    - { link: 'https://road-to-php.com/', title: 'The Road to PHP 8.1' }
    - { link: /blog/new-in-php-81, title: 'New in PHP 8.1', description: ' — A comprehensive list of all things new in PHP 8.1' }
    - { link: /blog/php-enums, title: 'Enums in PHP 8.1' }
    - { link: /blog/php-81-readonly-properties, title: 'Readonly properties in PHP 8.1' }
    - { link: /blog/php-81-new-in-initializers, title: 'New in initializers in PHP 8.1' }
    - { link: /blog/fibers-with-a-grain-of-salt, title: 'Fibers with a grain of salt' }
---

I did a very quick performance test because I wanted to know the impact of PHP 8.1 on my real-life projects.

{{ ad:carbon }}

These benchmarks were taken on my local machine, and only meant to measure the relative difference between PHP 8.0 and 8.1. I benchmarked a page that showed lots of data, with lots of classes loaded, as I expected that [inheritance cache](/blog/new-in-php-81#performance-improvements-pr) would have the largest performance impact.

<table>
<tr class="table-head">
    <td>Metric</td>
    <td>PHP 8.0</td>
    <td>PHP 8.1</td>
</tr>

<tr>
    <td>Requests per second (#/sec)</td>
    <td>32.02</td>
    <td>34.75</td>
</tr>

<tr>
    <td>Time per request (ms)</td>
    <td>31.232</td>
    <td>28.777</td>
</tr>
</table>

The results seem to be in line what what Dmitry originally reported when he added this feature: a 5-8% performance increase.

{{ cta:mail }}

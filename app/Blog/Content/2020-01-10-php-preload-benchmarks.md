---
title: 'Preloading benchmarks in PHP 7.4'
next: new-in-php-74
meta:
    description: 'Some benchmarks with the new preloading feature in PHP 7.4'
tag: src/content/tags/php-74.yaml
footnotes:
    - { link: /blog/preloading-in-php-74, title: 'Preloading in PHP 7.4' }
    - { link: /blog/new-in-php-74, title: "What's new in PHP 7.4" }
    - { link: /blog/short-closures-in-php, title: 'Short closures in PHP 7.4' }
    - { link: /blog/typed-properties-in-php-74, title: 'Typed properties in PHP 7.4' }
---

After writing about [how preloading works](/blog/preloading-in-php-74), it's time to measure its impact in practice.
Before diving into results, we need to make sure we're all on the same page: what we're measuring, and what not. 

Because I want to know whether preloading will have a practical impact on my projects, I'll be running benchmarks on a real project, on the homepage of my hobby project [aggregate.stitcher.io](*https://aggregate.stitcher.io/). 

This project is a Laravel project, and will obviously do some database calls, view rendering etc. I want to make clear that these benchmarks don't tell anything about the performance of Laravel projects, they *only* measure the relative performance gains preloading could offer.

Let me repeat that again, just to make sure no one draws wrong conclusions from these results: my benchmarks will *only* measure whether preloading has a relative performance impact compared to not using it. These benchmarks say nothing about how much performance gain there is. This will depend on several variables: server load, the code being executed, what page you're on, etc.

Let's set the stage.

{{ ad:carbon }}

## Preloading Setup

Since I don't want to measure how much exactly will be gained by using preloading or not, I decided to run these benchmarks on my local machine, using Apache Bench. I'll be sending 5000 requests, with 50 concurrent requests at a time.
 The webserver is nginx, using php-fpm. Because there were some bugs in early versions of preloading, we're only able to successfully run our benchmarks as early as PHP 7.4.2.

I'll be benchmarking three scenarios: one with preloading disabled, one with all Laravel and application code preloaded, and one with an optimised list of preloaded classes. The reasoning for that latter one is that preloading also comes with a memory overhead, if we're only preloading "hot" classes — classes that are used very often — we might be able to find a sweet spot between performance gain and memory usage.

## Preloading disabled

We start php-fpm and run our benchmarks:

```
./php-7_4_2/sbin/php-fpm --nodaemonize

ab -n 5000 -c 50 -l http://aggregate.stitcher.io.test:8080/discover
```

These were the results: we're able to process `64.79` requests per second, with an average time of `771ms` per request.
This is our baseline scenario, we can compare the next results to this one.

## Naive preloading

Next we'll preload all Laravel and application code. This is the naive approach, because we're never using all Laravel classes in a request. Because we're preloading many more files than strictly needed, we'll have to pay a penalty for it. In this case 1165 classes and their dependencies were preloaded, resulting in a total of 1366 functions and 1256 classes to be preloaded.

Like I mentioned before,you can read that info from `<hljs prop>opcache_get_status</hljs>`:

```php
<hljs prop>opcache_get_status</hljs>()['preload_statistics'];
```

Another metric we get from `<hljs prop>opcache_get_status</hljs>` is the memory used for preloaded scripts. In this case it's 17.43 MB.
Even though we're preloading more code than we actually need, naive preloading already has a positive impact on performance.


<table>
<tr class="table-head">
    <td></td>
    <td class="right">requests/second</td>
    <td class="right">time per request</td>
</tr>
<tr>
    <td>No preloading</td>
    <td class="right">64.79</td>
    <td class="right">771ms</td>
</tr>
<tr>
    <td>Naive preloading</td>
    <td class="right">79.69</td>
    <td class="right">627ms</td>
</tr>
</table>

You can already see a performance gain: we're able to manage more requests per second, and the average amount of time to process one request has dropped with ±20%.

{{ cta:mail }}

## Optimised

Finally we want to compare the performance gain when we're using an optimised preloading list. For testing purposes I started the server without preloading enabled, and dumped all classes that are used within that request:

```php
<hljs prop>get_declared_classes</hljs>();
```

Next, I only preloaded these classes, 427 in total. Together with all their dependencies this makes for 643 classes and 1034 functions being preloaded, occupying about 11.76 MB of memory.

These are the benchmark results for this setup:

<table>
<tr class="table-head">
    <td></td>
    <td class="right">requests/second</td>
    <td class="right">time per request</td>
</tr>
<tr>
    <td>No preloading</td>
    <td class="right">64.79</td>
    <td class="right">771ms</td>
</tr>
<tr>
    <td>Naive preloading</td>
    <td class="right">79.69</td>
    <td class="right">627ms</td>
</tr>
<tr>
    <td>Optimised preloading</td>
    <td class="right">86.12</td>
    <td class="right">580ms</td>
</tr>
</table>

That's around a 25% performance gain compared to not using preloading, and an 8% gain compared to using the naive approach. There's a flaw with this setup though, since I generated an optimised preloading list for one specific page. In practice you would probably need to preload more code, if you want all your pages covered.

Another approach could be to monitor which classes are loaded how many times over the period of several hours or days on your production server, and compile a preload list based on those metrics
It's safe to say that preloading — even using the naive "preload everything" approach — has a positive performance impact, also on real-life projects built upon a full-blown framework.
How much exactly there is to be gained will depend on your code, your server and the framework you're using. I'd say go try it out! 

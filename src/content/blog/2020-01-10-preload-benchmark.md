After writing about [how preloading works](*/blog/preloading-in-php-74), it's time to measure its impact in practice.
Before diving into results, we need to make sure we're all on the same page: what we're measuring, and what not. 

Because I want to know whether preloading will have a practical impact on my projects, I'll be running benchmarks on a real project, on the homepage of my hobby project [aggregate.stitcher.io](*https://aggregate.stitcher.io/). 

This project is a Laravel project, and will obviously do some database calls, view rendering etc. I want to make clear that these benchmarks don't tell anything about the performance of Laravel projects, they *only* measure the relative performance gains preloading could offer.

Let me repeat that again, just to make sure no one draws wrong conclusions from these results: my benchmarks will *only* measure whether preloading has a relative performance impact compared to not using it. These benchmarks say nothing about how much performance gain there is. This will depend on several variables: server load, the code being executed, what page you're on, etc.

Let's set the stage.

{{ ad:carbon }}

## Setup

Since I don't want to measure how much exactly will be gained by using preloading or not, I decided to run these benchmarks on my local machine, using Apache Bench. I'll be sending 5000 requests, with 50 concurrent requests at a time.

 The webserver is nginx, using php-fpm. Because there are [some bugs](*https://bugs.php.net/bug.php?id=78918) in early versions of preloading, we're only able to successfully run our benchmarks as early as PHP 7.4.2, which is not released yet. I've used a dev build for all my testing. 

The project itself is built in Laravel, and it's code (including the preload script) can be found [on GitHub](*https://github.com/brendt/aggregate.stitcher.io).

Finally, I'll be benchmarking three scenarios: one with preloading disabled, one with all Laravel and application code preloaded, and one with an optimised list of preloaded classes. I'll explain these scenarios one by one.

## Preloading disabled

This is our baseline scenario, we start php-fpm and run our benchmarks:

```
./php-7_4_2/sbin/php-fpm --nodaemonize

ab -n 5000 -c 50 -l http://aggregate.stitcher.io.test:8080/
```

Here are the results:

```
Requests per second:    64.79 [#/sec] (mean)
Time per request:       771.667 [ms] (mean)
```

## Naive preloading

Next we'll preload all Laravel and application code. This is the naive approach, because we're never using all Laravel classes in a request. Because we're preloading many more files than strictly needed, we'll have to pay a penalty for it. In this case 1165 classes and their dependencies were preloaded, resulting in a total of 1366 functions and 1256 classes to be preloaded.

If you're wondering how we can measure the exact amount of files that were preloaded: we can read that info from `opcache_get_status`:

```php
<hljs prop>opcache_get_status</hljs>()['preload_statistics'];
```

Another metric we get from `opcache_get_status` is the memory used for preloaded scripts. In this case it's around 17.43 MB.

Even though we're preloading more code than we actually need, naive preloading already has a positive impact on performance.

```
Requests per second:    79.69 [#/sec] (mean)
Time per request:       627.440 [ms] (mean)
```

That's around 20% performance gain compared to not using preloading at all. 

## Optimised

Finally we want to compare the performance gain when we're using an optimised preloading list. For testing purposes I started the server without preloading enabled, and dumped all classes that are used within that request:

```php
<hljs prop>get_declared_classes</hljs>();
```

Next, I only preloaded these classes, 427 in total. Together with all their dependencies this makes for 643 classes and 1034 being preloaded, occupying about 11.76 MB of memory.

These are the benchmark results for this setup:

```
Requests per second:    86.12 [#/sec] (mean)
Time per request:       580.558 [ms] (mean)
```

That's around a 25% performance gain compared to not using preloading, and an 8% gain compared to using the naive approach.

There's a flaw with this setup though, since I generated an optimised preloading list for one specific page. In practice you would probably need to preload more code, if you want all your pages covered.

Another approach could be to monitor which classes are loaded how many times over the period of several hours on your production server, and compile a preload list based on those metrics. Unfortunately I'm not able to test this approach out yet, since I'm not using preloading in production yet.

## In summary

Who doesn't like a graph when it comes to benchmarks? Let's compare the amount of requests per second and time per request for all three scenarios.

![](/resources/img/blog/preload/preload-1.svg)

Keep in mind that in case of requests per second, more is better. In case of time per request, less is better.

I think it's safe to say that preloading, even with a naive approach; will have a positive performance impact, also on real-life projects built upon a full-blown framework.

How much exactly there is to be gained will depend on your code, your server and the framework you're using. I'd say go try it out! 

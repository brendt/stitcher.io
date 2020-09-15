For those interested in [the JIT in PHP 8](/blog/php-jit), I did some benchmarks for you in real-world web application scenario. Be aware that these benchmarks don't say anything about whether the JIT is useful or not, they only show whether it can improve the performance of your average web application, or not.

{{ ad:carbon }}

## Setup

These benchmarks are run on my local machine. As so, they don't say anything about absolute performance gains, we're only able to make conclusions what kind of relative impact the JIT has on our code.

I'll be using one of [my hobby projects](*https://github.com/brendt/aggregate.stitcher.io), written in Laravel. Since these benchmarks were run on the first alpha version of PHP 8, I had to manually fix some deprecation warnings in Laravel's source code, all locally.

Finally: I'll be running PHP FPM, configured to spawn 20 child processes, and I'll always make sure to only run 20 concurrent requests at once, just to eliminate any extra performance hits on the FPM level. Sending these requests is done using the following command, with ApacheBench:

```
ab -n 100 -c 20 -l http://aggregate.stitcher.io.test:8081/discover
``` 

## JIT Setup

The JIT setup requires a section on its own. Honestly, this is one of the most confusing ways of configuring a PHP extension I've ever seen, and I'm afraid the syntax is here to stay, since we're too close to [PHP 8's feature freeze](/blog/the-latest-php-version) for another RFC to make changes to it. 

So here goes:

The JIT is enabled by specifying `opcache.jit_buffer_size` in `php.ini`. If this directive is excluded, the default value is set to 0, and the JIT won't run.

Next, there are several JIT control options, they are all stored in a single directive called `opcache.jit` and could, for example, look like this:

```ini
opcache.jit_buffer_size=100M
opcache.jit=1235
```

The [RFC](*https://wiki.php.net/rfc/jit) lists the meaning of each number. Mind you: this is not a bit mask, each number simply represents another configuration option. The RFC lists the following options:

#### O — Optimization level

<table>
    <tr><td>0</td> <td>don't JIT</td></tr>
    <tr><td>1</td> <td>minimal JIT (call standard VM handlers)</td></tr>
    <tr><td>2</td> <td>selective VM handler inlining</td></tr>
    <tr><td>3</td> <td>optimized JIT based on static type inference of individual function</td></tr>
    <tr><td>4</td> <td>optimized JIT based on static type inference and call tree</td></tr>
    <tr><td>5</td> <td>optimized JIT based on static type inference and inner procedure analyses</td></tr>
</table>

#### T — JIT trigger

<table>
    <tr><td>0</td> <td>JIT all functions on first script load</td></tr>
    <tr><td>1</td> <td>JIT function on first execution</td></tr>
    <tr><td>2</td> <td>Profile on first request and compile hot functions on second request</td></tr>
    <tr><td>3</td> <td>Profile on the fly and compile hot functions</td></tr>
    <tr><td>4</td> <td>Compile functions with @jit tag in doc-comments</td></tr>
    <tr><td>5</td> <td>Tracing JIT</td></tr>
</table>

#### R — register allocation

<table>
    <tr><td>0</td> <td>don't perform register allocation</td></tr>
    <tr><td>1</td> <td>use local liner-scan register allocator</td></tr>
    <tr><td>2</td> <td>use global liner-scan register allocator</td></tr>
</table>

#### C — CPU specific optimization flags

<table>
    <tr><td>0</td> <td>none</td></tr>
    <tr><td>1</td> <td>enable AVX instruction generation</td></tr>
</table>

One small gotcha: the RFC lists these options in reverse order, so the first digit represents the `C` value, the second the `R`, and so on.

Anyways, the RFC proposes `1235` as the best default, it will do maximum jitting, profile on the fly, use a global liner-scan register allocator — whatever that might be — and enables AVX instruction generation. 

In my benchmarks, I'll use several variations of JIT configuration, in order to compare the differences.

So let's start benchmarking!

## Establishing a baseline

First it's best to establish whether the JIT is working properly or not. We know from the RFC that it does have a significant impact on calculating a Mandelbrot — something most of us probably don't do in our web apps. 

So let's start with that example. I copied some mandelbrot code and accessed it via the same HTTP application I'll run the next benchmarks on. These are the results:

<table>
<tr class="table-head">
    <td></td>
    <td class="right">requests/second (more is better)</td>
</tr>
<tr>
    <td>Mandelbrot without JIT</td>
    <td class="right">15.24</td>
</tr>
<tr>
    <td>Mandelbrot with JIT</td>
    <td class="right">38.99</td>
</tr>
</table> 

Great, it looks like the JIT is working! That's more than a two time performance increase. Let's more on to our first real-life comparison. We're going to start slow: the JIT configured with `1231`, and 100 MB of buffer size.

The page we're benchmarking shows an overview of posts, so there's some recursion happening, and were touching several core parts of the Laravel as well: routing, DI, ORM, authentication. 

<table>
<tr class="table-head">
    <td></td>
    <td class="right">requests/second (more is better)</td>
</tr>
<tr>
    <td>No JIT</td>
    <td class="right">6.48</td>
</tr>
<tr>
    <td>JIT enabled (<code>1231</code>, 100M buffer)</td>
    <td class="right">6.33</td>
</tr>
</table>

Hm. A decrease in performance enabling the JIT? Sure that's possible! What the [JIT does](/blog/php-jit) is look at the code when its executing, discover "hot" parts of the code, and optimise those for the next run as machine code.

With the current configuration, analysing the code will happen on the fly, on every request. If there's little or no code to optimise, it's natural that there will be a performance price to pay.

So let's test a different setup, the one the RFC proposes as the most optimal one: `1235`

<table>
<tr class="table-head">
    <td></td>
    <td class="right">requests/second (more is better)</td>
</tr>
<tr>
    <td>No JIT</td>
    <td class="right">6.48</td>
</tr>
<tr>
    <td>JIT enabled (<code>1235</code>, 100M buffer)</td>
    <td class="right">6.75</td>
</tr>
</table>

Here we see an increase, albeit a teeny-tiny one. Turns out there were some parts that could be optimised, and their performance gain outweighed the performance cost.

There's two more things to test: what if we don't profile on every request, but instead only at the start, that's what the `T` option is for: _2 — Profile on first request and compile hot functions on second request_. 

In other words, let's use `1225` as the JIT option.

<table>
<tr class="table-head">
    <td></td>
    <td class="right">requests/second (more is better)</td>
</tr>
<tr>
    <td>No JIT</td>
    <td class="right">6.48</td>
</tr>
<tr>
    <td>JIT enabled (<code>1235</code>, 100M buffer)</td>
    <td class="right">6.75</td>
</tr>
<tr>
    <td>JIT enabled (<code>1225</code>, 100M buffer)</td>
    <td class="right">6.78</td>
</tr>
</table>

Once again a — small is an understatement — increase of performance!

One thing I'm wondering though: if we're only profiling on the first request, there probably are some parts of the code that will be missed out on optimisations; that's something someone will probably need to do some more research on.

So I suspect using `1225` in benchmarks has a positive impact because we're always requesting the same page, but in practice this probably will be a less optimal approach.

Finally, let's bump the buffer limit. Let's give the JIT a little more room to breath with 500MB of memory:

<table>
<tr class="table-head">
    <td></td>
    <td class="right">requests/second (more is better)</td>
</tr>
<tr>
    <td>No JIT</td>
    <td class="right">6.48</td>
</tr>
<tr>
    <td>JIT enabled (<code>1235</code>, 100M buffer)</td>
    <td class="right">6.75</td>
</tr>
<tr>
    <td>JIT enabled (<code>1235</code>, 500M buffer)</td>
    <td class="right">6.52</td>
</tr>
</table>

A slight decrease in performance. One I cannot explain to be honest. I'm sure someone smarter than me can provide us the answer though!

---

So, that concludes my JIT testing. As expected: the JIT probably won't have a significant impact on web applications, at least not right now. 

I won't discuss my thoughts on whether the JIT itself is a good addition or not in this post, let's have those discussions together [over here](*https://news.ycombinator.com/item?id=23721344)!  

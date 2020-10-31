For those interested in [the JIT in PHP 8](/blog/php-jit), I did some benchmarks for you in real-world web application scenario. Be aware that these benchmarks don't say anything about whether the JIT is useful or not, they only show whether it can improve the performance of your average web application, or not.

{{ ad:carbon }}

## Setup

Let's set the scene first. These benchmarks were run on my local machine. As so, they don't say anything about absolute performance gains, I'm only interested in making conclusions about the relative impact the JIT has on real-life code.

I'll be running PHP FPM, configured to spawn 20 child processes, and I'll always make sure to only run 20 concurrent requests at once, just to eliminate any extra performance hits on the FPM level. Sending these requests is done using the following command, with ApacheBench:

```
ab -n 100 -c 20 -l http://aggregate.stitcher.io.test:8081/discover
``` 

{{ cta:mail }}

## JIT Setup

With the project in place, let's configure the JIT itself. The JIT is enabled by specifying the `<hljs prop>opcache.jit_buffer_size</hljs>` option in `php.ini`. If this directive is excluded, the default value is set to 0, and the JIT won't run.

```ini
<hljs prop>opcache.jit_buffer_size</hljs>=100M
```

You'll also want to set a JIT mode, which will determine how the JIT will monitor and react to hot parts of your code. You'll need to use the `<hljs prop>opcache.jit</hljs>` option. Its default is set to `tracing`, but you can override it using `function`:

```ini
<hljs prop>opcache.jit</hljs>=function
; opcache.jit=tracing
```

In our real-life benchmarks, I'll compare both modes with each other.
So let's start benchmarking!

## Establishing a baseline

First it's best to establish whether the JIT is working properly or not. We know from the RFC that it does have a significant impact on calculating a fractal. So let's start with that example. I copied the mandelbrot example from the RFC, and accessed it via the same HTTP application I'll run the next benchmarks on:

```php
public function index()
{
    for ($y = -39; $y < 39; $y++) {
        <hljs prop>printf</hljs>("\n");

        for ($x = -39; $x < 39; $x++) {
            $i = $this-><hljs prop>mandelbrot</hljs>(
                $x / 40.0,
                $y / 40.0
            );

            if ($i == 0) {
                <hljs prop>printf</hljs>("*");
            } else {
                <hljs prop>printf</hljs>(" ");
            }
        }
    }

    <hljs prop>printf</hljs>("\n");
}

private function mandelbrot($x, $y)
{
    $cr = $y - 0.5;
    $ci = $x;
    $zi = 0.0;
    $zr = 0.0;
    $i = 0;

    while (1) {
        $i++;
        
        $temp = $zr * $zi;
        
        $zr2 = $zr * $zr;
        $zi2 = $zi * $zi;
        
        $zr = $zr2 - $zi2 + $cr;
        $zi = $temp + $temp + $ci;

        if ($zi2 + $zr2 > 16) {
            return $i;
        }

        if ($i > 5000) {
            return 0;
        }
    }
}
```

After running `ab` for a few hundred requests, we can see the results:

<table>
<tr class="table-head">
    <td></td>
    <td class="right">requests/second (more is better)</td>
</tr>
<tr>
    <td>Mandelbrot without JIT</td>
    <td class="right">3.60</td>
</tr>
<tr>
    <td>Mandelbrot with tracing JIT</td>
    <td class="right">41.36</td>
</tr>
</table>

Great, it looks like the JIT is working! That's even a ten times performance increase! Having verified it works as expected, let's move on to our first real-life comparison. We're going to compare no JIT with the function and tracing JIT; using 100MB of memory. The page we're going to benchmark shows an overview of posts, so there's some recursion happening. We're also touching several core parts of Laravel as well: routing, the dependency container, as well as the ORM layer. 

<div class="sidenote">
<h2>Side note:</h2>

If you want to verify whether the JIT is running, you can use `<hljs prop>opcache_get_status</hljs>()`, it has a `jit` entry which lists all relevant information:

```php
dd(<hljs prop>opcache_get_status</hljs>()['jit']);

// array:7 [▼
//   "enabled" => true
//   "on" => true
//   "kind" => 5
//   "opt_level" => 4
//   "opt_flags" => 6
//   "buffer_size" => 104857584
//   "buffer_free" => 104478688
// ]
```
</div>

<table>
<tr class="table-head">
    <td></td>
    <td class="right">requests/second (more is better)</td>
</tr>
<tr>
    <td>No JIT</td>
    <td class="right">63.56</td>
</tr>
<tr>
    <td>Function JIT</td>
    <td class="right">66.32</td>
</tr>
<tr>
    <td>tracing JIT</td>
    <td class="right">69.45</td>
</tr>
</table>

Here we see the results: enabling the JIT only has a slight improvement. In fact, running the benchmarks over and over, the results differ slightly every time: I've even seen cases where a JIT enabled run performs worse than the non JIT'ed version. Before drawing final conclusions, let's bump the memory buffer limit. We'll give the JIT a little more room to breath with 500MB of memory instead of 100MB.

<table>
<tr class="table-head">
    <td></td>
    <td class="right">requests/second (more is better)</td>
</tr>
<tr>
    <td>No JIT</td>
    <td class="right">71.69</td>
</tr>
<tr>
    <td>Function JIT</td>
    <td class="right">72.82</td>
</tr>
<tr>
    <td>Tracing JIT</td>
    <td class="right">70.11</td>
</tr>
</table>

As you can see: a case of the JIT performing worse. Like I said at the beginning of this post: I want to measure the relative the JIT has on real-life web projects. It's clear from these tests that sometimes there might be benefits, but it's in no way as noticeable as the fractal example we started out with. I admit I'm not really surprised by that. Like I wrote before: the're very little hot code to be optimised in real-life applications, we're only rarely doing fractal-like computations.

So am I saying there's no need for the JIT? Not quite, I think the JIT can open up new areas for PHP: areas where complex computations do benefit from JIT'ed code. I'm thinking about machine learning, AI, stuff like that. The JIT _might_ give opportunities to the PHP community that didn't exist yet, but it's unclear to say anything with certainty at this point. 

---

So, that concludes my JIT testing. As expected: the JIT probably won't have a significant impact on web applications, at least not right now. 

I won't discuss my thoughts on whether the JIT itself is a good addition or not in this post, let's have those discussions together [over here](*https://news.ycombinator.com/item?id=23721344)!  

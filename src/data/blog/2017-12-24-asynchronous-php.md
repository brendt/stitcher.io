We're working on a new package at Spatie. 
It's called [spatie/async](*https://github.com/spatie/async) and meant to do asynchronous parallel processing in PHP.

Parallel processing in PHP might seem like an edge case for many web developers, 
but let's take a look at a few use-cases we see at Spatie:

- [Image optimisation](*https://github.com/spatie/laravel-medialibrary)
- PDF rendering
- [Concurrent site crawling](*https://github.com/spatie/crawler)
- [Code generators](*https://github.com/spatie/schema-org)
- Static site generators - like Stitcher

We wanted to create an easy-to-use package, yet one that could solve our use cases.
Some of the packages listed above will not use the new `spatie/async` package,
because there's also a queueing system provided with Laravel.

This is how asynchronous code with our package looks like.

```php
use Spatie\Async\Process;

$pool = Pool::create();

foreach (range(1, 5) as $i) {
    $pool[] = async(function () use ($i) {
        // Something to execute in a child process.
    })->then(function (int $output) {
        // Handle output returned from the child process.
    })->catch(function (Exception $exception) {
        // Handle exceptions thrown in the child process.
    });
}

await($pool);
```

## Outperforming Amp

If you're into parallel PHP, you probably heard of [Amp](*https://github.com/amphp) and [ReactPHP](*https://github.com/reactphp).
Our package aims not to compete with those two, as it only solves one tiny aspect of parallelism in PHP.

We did however use both the packages to run some benchmarks against. 
Let's take a look at the results.

![Comparing Amp, spatie/async and ReactPHP](/img/blog/async/benchmarks.png)

You can see we have a little performance gain over Amp. 
ReactPHP however is a lot faster. Note that ReactPHP only manages processes, 
and doesn't allow for easy passing of closures to the child process. 
Something both our package and Amp does allow.

The benchmark code can be found [here](*https://github.com/spatie/async-benchmark).

So what's making our package faster and different than Amp? A few things:

- We're only using signals for inter process communication (IPC), instead of sockets.
Since PHP 7.1 we can use [pcntl_async_signals](*http://php.net/manual/en/function.pcntl-async-signals.php)
 to handle signals in a way so there's little to none performance cost.
- We don't provide a lot of the functionality that's in Amp. 
That's why our package will not be able to solve all the things Amp can.
- Because we're relying on signals, we don't support Windows.
Which, for our use cases, is no requirement.

We're happy with the results. 
I was able to plug this new package into Stitcher in about half an hour.
The package is still in its infancy, so there we'll have to work on it a lot more.
But the first results are pretty cool! 

## About process signals

Processes in UNIX systems can send signals to each other. 
Depending on what kind of signal is received, a process will act different.
Signals are handled by the ke
rnel, so they are pretty low level.
Before PHP 7.1 though, you had to `declare(ticks=1)` to use asynchronous signals in a reliable way.
This means that PHP will check for signals much more often, but it also introduces a lot of overhead:

> A tick is an event that occurs for every N low-level tickable statements executed by the parser within the declare block. The value for N is specified using ticks=N within the declare block's directive section.
  
With PHP 7.1, a new way of handling interrupts sent by the kernel.

> Zend Engine in PHP 7.1 was extended with ability of safe time-out and interrupt handling. Actually, PHP VM checks for EG(vm_interrupt) flag on each loop iteration, user function entry or internal function exit, and call callback function if necessary.
  
By using `pcntl_async_signals(true)`, PHP will now check for signals in a much more performant way.
A more in-depth explanation can be found in the [rfc](*https://wiki.php.net/rfc/async_signals),
submitted by Dmitry Stogov.

It's thanks to this mechanism that we're able to act on process status changes in a real asynchronous way, 
without having to rely on sockets or process status polling.

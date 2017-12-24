We're working on a new package at Spatie. 
It's called [spatie/async](*https://github.com/spatie/async) and meant to do asynchronous parallel processing in PHP.

Parallel processing in PHP might seem like an edge case for many web developers, 
but let's take a look at a few use-cases we see at Spatie:

- [Image optimisation](*https://github.com/spatie/laravel-medialibrary)
- PDF rendering
- [Concurrent site crawling](*https://github.com/spatie/crawler)
- [Code generators](*https://github.com/spatie/schema-org)
- Static site generators - like Stitcher

We wanted to create an easy-to-use package, yet one that could solve some of our use cases.
Some of the packages listed above will not use the new `spatie/async` package,
because there's also a queueing system provided with Laravel.

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

So what's making our package faster than Amp? A few things:

- We're only using signals for inter process communication (IPC), instead of sockets.
Since PHP 7.1 we can use [pcntl_async_signals](*http://php.net/manual/en/function.pcntl-async-signals.php)
 to handle signals in a way so there's little to none performance cost.
- We don't provide a lot of the functionality that's in Amp. 
That's why our package will not be able to solve all the things Amp can.

We're happy with the results though. 
I was able to plug this new package into Stitcher in about half an hour.
The package is still in its infancy, so there we'll have to work on it a lot more.
But the first results are pretty cool! 

To finish, this is the code you can write with our package:

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

I'll write about the implementation in Stitcher soon!

---
title: 'Running PHP code in parallel, the easy way'
meta:
    description: 'I made a no-nonsense wrapper around pcntl_fork'
footnotes:
    - { title: 'Opinion-driven design', link: /blog/opinion-driven-design }
    - { title: 'What about typed request classes?', link: /blog/what-about-request-classes }
    - { title: 'What about config builders?', link: /blog/what-about-config-builders }
---

Less is more. You've heard that before, right? Keep it in mind, I'm going to show you something. 

There are a few good and robust solutions to run PHP code in parallel already; and yet, we've made our own implementation. I want to explain why. First, let's set the scene: I want to run PHP code in parallel. Here are some of my use cases:

- to test race conditions for our command-bus, when running PHPUnit tests;
- to do a bunch of HTTP requests in parallel; and also
- to generate this blog faster by allowing my static generator to work on multiple processes.

My use cases have two requirements in common: run a arbitrary amount of functions in parallel, and wait until all of them are finished. Let's look at the solutions available today.

**AmpPHP** has a package called [parallel-functions](*https://github.com/amphp/parallel-functions). It looks like this:

```php
use <hljs type>Amp\Promise</hljs>;
use function <hljs type>Amp\ParallelFunctions\</hljs><hljs prop>parallelMap</hljs>;

$values = <hljs type>Promise\</hljs><hljs prop>wait</hljs>(
    <hljs prop>parallelMap</hljs>([1, 2, 3], function ($time) {
        \<hljs prop>sleep</hljs>($time);
    
        return $time * $time;
    })
);
```

For my use cases, I've got a few problems with this implementation:

- it uses promises, which are very good for more complex async work, but are only overhead for me;
- Amp's API and its use of functions feels very clunky to me, but that's rather subjective, I realise that; and finally 
- if you need a framework in your child processes, you'll need to boot it manually.

Moving on to **ReactPHP**, they don't have an out-of-the-box solution like Amp, but they do offer [the low-level components](*https://reactphp.org/child-process/):

```php
$loop = <hljs type>React\EventLoop\Factory</hljs>::<hljs prop>create</hljs>();

$process = new <hljs type>React\ChildProcess\Process</hljs>('php child-process.php');

$process-><hljs prop>start</hljs>($loop);

$process-><hljs prop>stdout</hljs>-><hljs prop>on</hljs>('data', function ($chunk) {
    echo $chunk;
});

$process-><hljs prop>on</hljs>('exit', function($exitCode, $termSignal) {
    echo 'Process exited with code ' . $exitCode . PHP_EOL;
});

$loop-><hljs prop>run</hljs>();
```

A few caveats with this implementation:

- ReactPHP always requires you to manually create an event loop which, again, is overhead for me; 
- they also work with promises; and finally
- they only offer the bare infrastructure to run processes in parallel, there's lots of manual setup work.

Finally, there's **Guzzle** with its [concurrent requests](*https://docs.guzzlephp.org/en/stable/quickstart.html#concurrent-requests):

```php
use <hljs type>GuzzleHttp\Client</hljs>;
use <hljs type>GuzzleHttp\Promise</hljs>;

$client = new <hljs type>Client</hljs>(['base_uri' => 'http://httpbin.org/']);

$promises = [
    'image' => $client-><hljs prop>getAsync</hljs>('/image'),
    'png'   => $client-><hljs prop>getAsync</hljs>('/image/png'),
    'jpeg'  => $client-><hljs prop>getAsync</hljs>('/image/jpeg'),
    'webp'  => $client-><hljs prop>getAsync</hljs>('/image/webp')
];

$responses = <hljs type>Promise\Utils</hljs>::<hljs prop>unwrap</hljs>($promises);
```

- Again, there's the overhead of promises; but more importantly
- Guzzle only works with HTTP requests, which only solves part of my problem.

---

Of all of the above, Amp's approach would have my preference, were it not that it still has quite a lot of overhead  for my simple use cases. Honestly, all I wanted to do was to run some functions in parallel and wait until all of them are finished. I don't want to be bothered by looking up documentation about the particular API a framework is using. Did I have to import a function here? How to unwrap promises? How to wait for everything to finish?

All of the above examples are great solutions for the 10% cases that require people to have lots of control, but what about the 90% of cases where you just want to do one thing as simply as possible?

Less is more. We often forget that in software design. We overcomplicate our solution "just in case" someone might need it, and forget about the 90% use case. It leads to frustration because developers have to look up documentation in order to understand how to use a framework, or they have to write lots of boilerplate to get their generic case to work.

So with all of that being said, you now know why I decided to make another library that has one simple goal: run functions in parallel and wait for the result. Here's what it looks like:

```php
$rssFeeds = <hljs type>Fork</hljs>::<hljs prop>new</hljs>()
    -><hljs prop>run</hljs>(
        <hljs keyword>fn</hljs> () => <hljs prop>file_get_contents</hljs>('https://stitcher.io/rss'),
        <hljs keyword>fn</hljs> () => <hljs prop>file_get_contents</hljs>('https://freek.dev/rss'),
        <hljs keyword>fn</hljs> () => <hljs prop>file_get_contents</hljs>('https://spatie.be/rss'),
    );
```

And that's it. It does one job, and does it well. And don't be mistaken: it's not because there's a simple API that it only offers simple functionality! Let me share a few more examples.

Parallel functions are able to return anything, including objects:

```php
$dates = <hljs type>Fork</hljs>::<hljs prop>new</hljs>()
    -><hljs prop>run</hljs>(
        <hljs keyword>fn</hljs> () => new <hljs type>DateTime</hljs>('2021-01-01'),
        <hljs keyword>fn</hljs> () => new <hljs type>DateTime</hljs>('2021-01-02'),
    );
```

They use process forks instead of fresh processes, meaning you don't need to manually boot your framework in every child process:

```php
[$users, $posts, $news] = <hljs type>Fork</hljs>::<hljs prop>new</hljs>()
    -><hljs prop>run</hljs>(
        <hljs keyword>fn</hljs> () => <hljs type>User</hljs>::<hljs prop>all</hljs>(),
        <hljs keyword>fn</hljs> () => <hljs type>Post</hljs>::<hljs prop>all</hljs>(),
        <hljs keyword>fn</hljs> () => <hljs type>News</hljs>::<hljs prop>all</hljs>(),
    );
```

They allow before and after bindings, just in case you need to do a little more setup work. In the previous example, Laravel actually needs to reconnect to the database in the child processes before it would work:

```php
[$users, $posts, $news] = <hljs type>Fork</hljs>::<hljs prop>new</hljs>()
    -><hljs prop>before</hljs>(<hljs keyword>fn</hljs> () => <hljs type>DB</hljs>::<hljs prop>connection</hljs>('mysql')-><hljs prop>reconnect</hljs>())
    -><hljs prop>run</hljs>(
        <hljs keyword>fn</hljs> () => <hljs type>User</hljs>::<hljs prop>all</hljs>(),
        <hljs keyword>fn</hljs> () => <hljs type>Post</hljs>::<hljs prop>all</hljs>(),
        <hljs keyword>fn</hljs> () => <hljs type>News</hljs>::<hljs prop>all</hljs>(),
    );
```

And finally, before and after bindings can be run both in the child process and parent process; and also notice how individual function output can be passed as a parameter to these `<hljs prop>after</hljs>` callbacks:

```php
<hljs type>Fork</hljs>::<hljs prop>new</hljs>()
    -><hljs prop>after</hljs>(
        <hljs prop>child</hljs>: <hljs keyword>fn</hljs> () => <hljs type>DB</hljs>::<hljs prop>connection</hljs>('mysql')-><hljs prop>close</hljs>(),
        <hljs prop>parent</hljs>: <hljs keyword>fn</hljs> (<hljs type>int</hljs> $amountOfPages) => 
            $this-><hljs prop>progressBar</hljs>-><hljs prop>advance</hljs>($amountOfPages),
    )
    -><hljs prop>run</hljs>(
        <hljs keyword>fn</hljs> () => <hljs type>Pages</hljs>::<hljs prop>generate</hljs>('1-20'),
        <hljs keyword>fn</hljs> () => <hljs type>Pages</hljs>::<hljs prop>generate</hljs>('21-40'),
        <hljs keyword>fn</hljs> () => <hljs type>Pages</hljs>::<hljs prop>generate</hljs>('41-60'),
    );
```

There are of course a few things this package doesn't do:

- there's no pool managing the amount of concurrent processes, you're in charge if you need to;
- there are no promises;
- [pcntl](*https://www.php.net/manual/en/book.pcntl.php) doesn't work on Windows and doesn't run in web requests;
- there's no behind the scenes exception handling, if a child fails it'll throw an exception and stop the process flow.

In other words: it's the perfect solution for the 90% case where you just want to run some functions in parallel and be done with it. If you need anything more than that, then the solutions listed above are a great start. There's also another package of ours called [`spatie/async`](*https://github.com/spatie/async) that doesn't work with promises but does offer pool configuration and extensive exception handling.

If you want to know more or want to try the package yourself, you can check it out on GitHub: [`spatie/fork`](*https://github.com/spatie/fork).

{{ cta:mail }}

Less is more. That's one of my core principles when coding. I prefer code that forces me to do something one way but always works, instead of a highly configurable framework that makes me wonder how to use it every time I look at it. I feel that many developers often get lost in a maze of high configurability and extensibility and forget their original end goal by doing so.

I hope this package can be of help for that group of people who fall in the 90% category.

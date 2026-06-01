---
title: 'My top-10 favourite functions in PHP'
---

More than once, I've been amazed by what's actually built-into PHP. Here are some of my personal favourite functions. 

## Levenshtein

"Levenshtein" is the name of an algorithm to determine the difference — aka "distance" — between two strings. The name comes — unsurprisingly — from its inventor: Vladimir Levenshtein.

It's a pretty cool function to determine how similar two related words or phrases are. For example: passing in `"PHP is awesome"` twice, will result in a "distance" of `0`:

```php
levenshtein("PHP is awesome", "PHP is awesome"); // 0
```

However, passing in two different phrases will result in a larger distance:

```php
levenshtein("Dark colour schemes", "are awesome"); // 13
```

Unsurprisingly, given how incompatible above two statements are 😉

## Easter dates

PHP has — believe it or not — a built-in function to determine the date of Easter for any given year. Given that Easter's date is determined by 
"the **first Sunday** after **the full Moon** that occurs on or **after the spring equinox**", I'm in awe of PHP being able to calculate it for me. 

Or maybe it simply is hard coded?

```php
date('Y-m-d', easter_date(2023)); // 2023-04-08
```

## Forks

Did you know PHP can be async? CLI versions of PHP have access to the `pcntl` functions, including the `pcntl_fork` function. This function is basically a wrapper for creating process forks, allowing one PHP process to spawn and manage several!

Here's a simple example using sockets to create an async child process in PHP:

```php
function async(Process $process): Process {
    socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $sockets);
    [$parentSocket, $childSocket] = $sockets;

    if (($pid = pcntl_fork()) == 0) {
        socket_close($childSocket);
        socket_write($parentSocket, serialize($process->execute()));
        socket_close($parentSocket);
        exit;
    }

    socket_close($parentSocket);

    return $process
        ->setStartTime(time())
        ->setPid($pid)
        ->setSocket($childSocket);
}
```

I actually wrote a little package that wraps everything in an easy-to-use API: [spatie/async](https://github.com/spatie/async).

## Metaphone?

Similar to `levenshtein`, `methaphone` can generate a phonetic representation of a given string:

```php
metaphone("Light color schemes!"); // LFTKLRSXMS
metaphone("Light colour schemes!"); // LFTKLRSXMS
```

## Built-in DNS

PHP understands DNS, apparently. It has a built-in function called `dns_get_record`, which does as its name implies: it gets a DNS record.

```php
dns_get_record("stitcher.io");

{
    ["host"] => "stitcher.io"
    ["class"] => "IN"
    ["ttl"] => 539
    ["type"] => "NS"
    ["target"] => "ns1.ichtushosting.com"
}

// …
```

{{ cta:dynamic }}

## Recursive array merging

I mainly wanted to include `array_merge_recursive` because, for a long time, I misunderstood what it did. I used to think you'd have to use it for merging multidimensional arrays, but that's not true!

It might be better to let [past-me explain it](/blog/merging-multidimensional-arrays-in-php) but, in summary, it works like this:

```php
$first = [
    'key' => 'original'
];

$second = [
    'key' => 'override'
];

array_merge_recursive($first, $second);

{
    ["key"] => {
        "original",
        "override",
    }
}
```

## Mail

PHP has a mail function. A function to send mail. I wouldn't use it, but it's there:

```php
mail(
    string $to,
    string $subject,
    string $message,
    array|string $additional_headers = [],
    string $additional_params = ""
): bool
```

## DL

Apparently, there's a function in PHP that allows you to dynamically load extensions, _while_ your script is running!

```php
if (! extension_loaded('sqlite')) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        dl('php_sqlite.dll');
    } else {
        dl('sqlite.so');
    }
}
```

## Blob… I mean glob

`glob` is a seriously awesome function: it finds pathnames according to a pattern. It's pretty easy to explain, but it's oh so useful:

```php
glob(__DIR__ . '/content/blog/*.md');
glob(__DIR__ . '/content/*/*.md');

{
    /path/to/content/blog/foo.md,
    /path/to/content/other/bar.md,
    …
}
```

## Sun info

Finally, PHP not only knows about Easter, it also knows about when the sun rises and sets, for any given date! It also requires a longitude and latitude, which of course makes sense because the sunrise and sunset times depend on your location:

```php
date_sun_info(
    timestamp: strtotime('2023-01-27'), 
    latitude: 50.278809, 
    longitude: 4.286095,
)

{
  ["sunrise"] => 1674804140
  ["sunset"] => 1674836923
  ["transit"] => 1674820532
  ["civil_twilight_begin"] => 1674802111
  ["civil_twilight_end"] => 1674838952
  ["nautical_twilight_begin"] => 1674799738
  ["nautical_twilight_end"] => 1674841325
  ["astronomical_twilight_begin"] => 1674797441
  ["astronomical_twilight_end"] => 1674843622
}
```

---

What's your favourite PHP function? Let me know on [Twitter](*https://twitter.com/brendt_gd)!

{{ cta:mail }}
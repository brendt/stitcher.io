---
title: 'My top-10 favourite functions in PHP'
---

More than once, I've been amazed by what's actually built-into PHP. Here are some of my personal favourite functions. 

## Levenshtein

"Levenshtein" is the name of an algorithm to determine the difference — aka "distance" — between two strings. The name comes — unsurprisingly — from its inventor: Vladimir Levenshtein.

It's a pretty cool function to determine how similar two related words or phrases are. For example: passing in `"PHP is awesome"` twice, will result in a "distance" of `0`:

```php
<hljs prop>levenshtein</hljs>("PHP is awesome", "PHP is awesome"); // 0
```

However, passing in two different phrases will result in a larger distance:

```php
<hljs prop>levenshtein</hljs>("Dark colour schemes", "are awesome"); // 13
```

Unsurprisingly, given how incompatible above two statements are 😉

## Easter dates

PHP has — believe it or not — a built-in function to determine the date of Easter for any given year. Given that Easter's date is determined by 
"the **first Sunday** after **the full Moon** that occurs on or **after the spring equinox**", I'm in awe of PHP being able to calculate it for me. 

Or maybe it simply is hard coded?

```php
<hljs prop>date</hljs>('Y-m-d', <hljs prop>easter_date</hljs>(2023)); // 2023-04-08
```

## Forks

Did you know PHP can be async? CLI versions of PHP have access to the `pcntl` functions, including the `<hljs prop>pcntl_fork</hljs>` function. This function is basically a wrapper for creating process forks, allowing one PHP process to spawn and manage several!

Here's a simple example using sockets to create an async child process in PHP:

```php
function <hljs prop>async</hljs>(<hljs type>Process</hljs> $process): Process {
    <hljs prop>socket_create_pair</hljs>(<hljs prop>AF_UNIX</hljs>, <hljs prop>SOCK_STREAM</hljs>, 0, $sockets);
    [$parentSocket, $childSocket] = $sockets;

    if (($pid = <hljs prop>pcntl_fork</hljs>()) == 0) {
        <hljs prop>socket_close</hljs>($childSocket);
        <hljs prop>socket_write</hljs>($parentSocket, <hljs prop>serialize</hljs>($process-><hljs prop>execute</hljs>()));
        <hljs prop>socket_close</hljs>($parentSocket);
        exit;
    }

    <hljs prop>socket_close</hljs>($parentSocket);

    return $process
        -><hljs prop>setStartTime</hljs>(<hljs prop>time</hljs>())
        -><hljs prop>setPid</hljs>($pid)
        -><hljs prop>setSocket</hljs>($childSocket);
}
```

I actually wrote a little package that wraps everything in an easy-to-use API: [spatie/async](https://github.com/spatie/async).

## Metaphone?

Similar to `<hljs prop>levenshtein</hljs>`, `<hljs prop>methaphone</hljs>` can generate a phonetic representation of a given string:

```php
<hljs prop>metaphone</hljs>("Light color schemes!"); // LFTKLRSXMS
<hljs prop>metaphone</hljs>("Light colour schemes!"); // LFTKLRSXMS
```

## Built-in DNS

PHP understands DNS, apparently. It has a built-in function called `<hljs prop>dns_get_record</hljs>`, which does as its name implies: it gets a DNS record.

```php
<hljs prop>dns_get_record</hljs>("stitcher.io");

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

I mainly wanted to include `<hljs prop>array_merge_recursive</hljs>` because, for a long time, I misunderstood what it did. I used to think you'd have to use it for merging multidimensional arrays, but that's not true!

It might be better to let [past-me explain it](/blog/merging-multidimensional-arrays-in-php) but, in summary, it works like this:

```php
$first = [
    'key' => 'original'
];

$second = [
    'key' => 'override'
];

<hljs prop>array_merge_recursive</hljs>($first, $second);

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
<hljs prop>mail</hljs>(
    <hljs type>string</hljs> $to,
    <hljs type>string</hljs> $subject,
    <hljs type>string</hljs> $message,
    <hljs type>array|string</hljs> $additional_headers = [],
    <hljs type>string</hljs> $additional_params = ""
): <hljs type>bool</hljs>
```

## DL

Apparently, there's a function in PHP that allows you to dynamically load extensions, _while_ your script is running!

```php
if (! <hljs prop>extension_loaded</hljs>('sqlite')) {
    if (<hljs prop>strtoupper</hljs>(<hljs prop>substr</hljs>(<hljs prop>PHP_OS</hljs>, 0, 3)) === 'WIN') {
        <hljs prop>dl</hljs>('php_sqlite.dll');
    } else {
        <hljs prop>dl</hljs>('sqlite.so');
    }
}
```

## Blob… I mean glob

`<hljs prop>glob</hljs>` is a seriously awesome function: it finds pathnames according to a pattern. It's pretty easy to explain, but it's oh so useful:

```php
<hljs prop>glob</hljs>(<hljs prop>__DIR__</hljs> . '/content/blog/*.md');
<hljs prop>glob</hljs>(<hljs prop>__DIR__</hljs> . '/content/*/*.md');

{
    /path/to/content/blog/foo.md,
    /path/to/content/other/bar.md,
    …
}
```

## Sun info

Finally, PHP not only knows about Easter, it also knows about when the sun rises and sets, for any given date! It also requires a longitude and latitude, which of course makes sense because the sunrise and sunset times depend on your location:

```php
<hljs prop>date_sun_info</hljs>(
    <hljs prop>timestamp</hljs>: <hljs prop>strtotime</hljs>('2023-01-27'), 
    <hljs prop>latitude</hljs>: 50.278809, 
    <hljs prop>longitude</hljs>: 4.286095,
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
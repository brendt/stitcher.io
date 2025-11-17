It took some going back and forth, but we're finally getting the pipe operator in PHP 8.5! In this post, we'll take a look at how it works and also _why_ you'd want to use it. 

<iframe width="560" height="347" src="https://www.youtube.com/embed/0gSvLttEQas" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

## The benefit of the pipe operator

Imagine some kind of input — a string, for example — and you want to perform a series of operations on it. Let's say you want to transform it into a slug.

```php
$input = ' Some kind of string. ';

$output = strtolower(
    str_replace(['.', '/', '…'], '',
        str_replace(' ', '-',
            trim($input)
        )
    )
);
```

You can probably think of a handful of other examples from your own code; and there are a number of problems with it.

- First, deeply nested function calls can spiral out of control fairly quickly;
- second, code formatting becomes a mess when you have to decide if you want each new argument on a new line, or only have a newline per function call; and
- third, this code should be read in reverse from the inside-out: it starts with `{php}trim()`, then moves to `{php}str_replace()`, and so on.

A possible improvement would be to introduce a temporary variable:

```php
$input = ' Some kind of string. ';

$temp = trim($input);
$temp = str_replace(' ', '-', $temp);
$temp = str_replace(['.', '/', '…'], '', $temp);

$output = strtolower($temp);
```

I would say this is already a lot better and solves all the issues I listed previously; but the downside is that we introduce a new temporary variable. It feels… icky. It's not a dealbreaker, and it's definitely better than deeply nested function calls. But what if we could make it just a tiny bit more convenient? That's what the pipe operator is for! Here's what this code will look like in PHP 8.5:

```php
$output = $input 
    |> trim(...)
    |> (fn (string $string) => str_replace(' ', '-', $string))
    |> (fn (string $string) => str_replace(['.', '/', '…'], '', $string))
    |> strtolower(...);
```

That's looking pretty good! Now, there are a couple of things to say about this example, so let's take a closer look.

## The "right side"

The pipe operator works by piping whatever is on the left side, to the right side. The "left side" is pretty simple to understand — any kind of input can go here: strings, arrays, objects, the output of a previous pipe. But what about the "right side" This is where you need to take a couple of things into account.

The first possibility is passing in a reference to a callable, a so-called [first-class callable](/blog/new-in-php-81#first-class-callable-syntax-rfc):

```php
$output = $input |> trim(...);
```

However, this option only works if said callable only takes _one argument_ — which will be the input provided to the pipe. In other words, the example above will be transformed behind the scenes into this:

```php
$output = trim($input);
```

But what if you want to pass the input to a function that takes _multiple arguments_? This is where you need to use a callable. These callables can be short or full closures, invokable classes; they can even be string or array references to callable functions — as long as they only take one input argument. Personally, I prefer to use short closures:

```php
$output = $input |> (fn (string $string) => str_replace(' ', '-', $string));
```

The fact that you need to use a closure does feel a little clunky — it's even more verbose than our `$temp` example! However, I still prefer using the pipe operator over `$temp` variables, because a "block of pipes" feels more like "code that belongs together" than when you'd use `$temp`.

On top of that, internals are actually working on a followup feature that would play very nice with the pipe operator. It's called [partial function application](https://wiki.php.net/rfc/partial_function_application_v2), and it would allow us to rewrite the previous example like so:

```php
// Doesn't work yet!
$output = $input |> str_replace(' ', '-', ?);
```

Notice that question mark? That's a placeholder for "missing input", and that's the gap the pipe operator could "fill in" when it arrives at this function. It would be really nice to combine the two, but for now we'll have to make due with using callables.

Despite that shortcoming, I still think the pipe operator is an amazing new feature, and I'm looking forward to using it when PHP 8.5 arrives! What are your thoughts? Let me know on [Discord](/discord)!
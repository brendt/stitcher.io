PHP has a lot of undiscovered native functions for many devs. Usually, we don't discover these functions until there is a true need for them. `array_chunk` is certainly one of these functions for me personally. In this article, we will discover what `array_chunk` is, what it does, and see it used in action.
 
 {{ ad:carbon }}
 
## `array_chunk` and how it works
 
`array_chunk` is a pretty nifty name for slicing an array into chunks. This is essentially what the function does, it reads an array, and splits it into chunks. 

Let me explain this in developer terms: we have an array, `[1, 2, 3, 4, 5, 6, 7, 8]`, we use `array_chunk` on the array, specifying we want chunks of 2 items. The output would look similar to below.

```
$input = [1, 2, 3, 4, 5, 6, 7, 8];

<hljs prop>array_chunk</hljs>($input, 2);

[
    0 => [1, 2], 
    1 => [1, 2], 
    2 => [1, 2], 
    3 => [1, 2],
]
```
 
Pretty cool eh? With this function, you can quickly see how it can be utilized for statistical purposes, especially with segment averaging.
 
This function accepts 3 parameters as follows:
 
-   The `array`
-   The size of the chunks required as an `int`
-   A `boolean` to instruct the functions to preserve the keys of the original array or to not. _Note_: the default is `false`.
 
## A real-life problem
 
Walk with me now through this scenario: my boss wants to know for each working week, the average profits from his shop. Each working week has 5 days. 

So, let's say for argument's sake say we have just queried the last 20 days of shop sales from the shop's database. The data returned populate our array with 20 entries and therefore has 4 working weeks. 

Now this leaves us with the problem at hand, we need to calculate the average sales across every 5 days for 4 weeks. Follow me through this next section to achieve the result.
 
## Average segments with `array_chunk`
 
We know our data array has 20 entries, and we know that we need an average of each week of sales (5 entries). Let's utilize `array_chunk` with a little bit of extra native PHP to do the calculations.
 
 ```php
$sales = [
    250.70, 220.10, 233, 243.50, 255,
    200, 300, 234, 350, 222,
    237.99, 200.30, 150.98, 201, 209,
    200, 300, 240, 203, 280,
];

// Split the array into groups of five, 
//  representing a 5 days working week.
$salesPerWeek = <hljs prop>array_chunk</hljs>($sales, 5);

// Map all items to their averages, week by week.
$averageSales = <hljs prop>array_map</hljs>(
    <hljs keyword>fn</hljs>(<hljs type>array</hljs> $items) => <hljs prop>array_sum</hljs>($items) / <hljs prop>count</hljs>($items),
    $salesPerWeek
);
```

Now, if we print the contents of `$averageSales` we will get something like the following:

```php
[
    240.46,
    261.2,
    199.854,
    244.6,
]
```

Let's break down the code for complete transparency:
 
-   First, we have our array with 20 entries of sales data for each day.
-   Next, we use `array_chunk` to split it into groups of five.
-   Then we use `array_map` on each chunk, and use `array_sum` to divide by the count of the chunk to give us the average.

And that is it!
 
This type of functionality could be used in many statistical applications that require segmentation. The example in this article tries to show you how `array_chunk` works in layman terms with a bit of a pretend use-case behind it. I hope this was interesting, if you would like to see any more of my content, please check out my blog, [https://www.codewall.co.uk/](*https://www.codewall.co.uk/)

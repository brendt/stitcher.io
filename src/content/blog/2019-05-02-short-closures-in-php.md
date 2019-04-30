Short closures are a way of writing shorter functions in PHP. 
This notation is useful when passing closures to functions like `array_map` or `array_filter`.

{{ ad:carbon }}

This is what they look like:

```php
// A collection of Post objects
$posts = [/* … */];

$ids = <hljs prop>array_map</hljs>(<hljs keyword>fn</hljs>($post) => $post-><hljs prop>id</hljs>, $posts);
``` 

Previously, you'd had to write this:

```php
$ids = <hljs prop>array_map</hljs>(function ($post) {
    return $post-><hljs prop>id</hljs>;
}, $posts);
```

Let's summarize how short closures can be used.

- Available as of PHP 7.4
- They start with the `fn` keyword
- They can only have _one_ expression, which is the return statement
- The `return` keyword can be omitted
- Arguments and return types can be type hinted

A more strictly typed way of writing the example above could be this:

```php
$ids = <hljs prop>array_map</hljs>(<hljs keyword>fn</hljs>(<hljs type>Post</hljs> $post): <hljs type>int</hljs> => $post-><hljs prop>id</hljs>, $posts);
``` 

Two more things to mention:

- References are allowed, both for the arguments as the return values
- The spread operator is also allowed

If you want to return a value by reference, the following syntax should be used:

```php
<hljs keyword>fn</hljs>&($x) => $x
```

In short, short closures allow the same functionality you'd expect from normal closures, 
with the exception of only allowing one expression.

## No multiline

You read it right: short closures can only have _one_ expression. 
This means you cannot have multiple lines in them.

The reasoning is as follows: the goal of short closures is to reduce verbosity. 
`fn` is of course shorter than `function` in all cases.
However, Nikita Popov, the creator of the RFC, argued that if you're dealing with multiline closures, 
there less to be gained by using short closures.

After all, multi line closures are by definition already more verbose,
being able to skip two keywords (`function` and `return`) wouldn't make much of a difference.

Whether you agree with this sentiment is up to you. 
While I can think of many one-line closures in my projects, 
there are also plenty of multi line ones, and I'll personally miss the short syntax in those cases.

It is possible that multi line short closures will be added though, 
but that's an RFC on its own.

## Values from outer scope

A major difference between short and normal closures is that short closures don't 
require the `use` keyword to be able to access data from the outer scope.

```php
$modifier = 5;

<hljs prop>array_map</hljs>(<hljs keyword>fn</hljs>($x) => $x * $modifier, $numbers);
```  

It's important to note that you're not allowed to modify variables from the outer scope.
Values are bound by value and not by reference. 
This means that you _could_ change `$modifier` within the short closure, 
though it wouldn't have effect on the `$modifier` variable in the outer scope.

One exception is of course the `$this` keyword, which acts exactly the same as normal closures:

```php
<hljs prop>array_map</hljs>(<hljs keyword>fn</hljs>($x) => $x * $this->modifier, $numbers);
```

## Future possibilities

I already mentioned multi line short closures, which is still a future possibility.
Another idea floating around is allowing the short closure syntax in classes, for example for getters and setters:

```php
class Post {
    private $title;
 
    <hljs keyword>fn</hljs> <hljs prop>getTitle</hljs>() => $this->title;
}
```

All in all, short closures are a welcome feature, though there is still area for improvement. 
The biggest one probably being multi line short closures.

Do you have any thoughts you'd like to share? 
Feel free to send a [tweet](*https://twitter.com/brendt_gd) or an [email](mailto:brendt@stitcher.io) my way! 


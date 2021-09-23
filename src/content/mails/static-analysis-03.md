We've discussed what static analysis is about from a high-level point of view and compared the different options out there; so the next question is: what can modern static analysers _actually_ do? 

I already showed one example, where a static analyser tells you that you've provided the wrong input:

```php
function foo(<hljs type>array</hljs> $input): void {}

<hljs prop>foo</hljs>(<hljs striped>'wrong input'</hljs>);
```

##### Error: Argument 1 of foo expects array, "wrong input" provided

Not only that, but Psalm, for example, also tells you that `$input` in the function is actually _never_ used:

```php
function foo(<hljs type>array</hljs> <hljs striped>$input</hljs>): void {}
```

##### Info: Param $input is never referenced in this method

But, there's more. Here's a conditional that will never reach the `<hljs keyword>elseif</hljs>` block:

```php
$condition = /* some kind of boolean expression */;

if ($condition) {
    // …
} elseif (<hljs striped>$condition</hljs>) {
    // …
}
```

##### Error: Elseif condition is always false.

These are isolated examples, but keep in mind that Psalm and PHPStan evaluate your whole codebase at once. They are able to detect these kinds of issues across files, in deeply nested structures, etc.

And that's where static analysis truly shines: where the human mind isn't able to keep an overview anymore, because there simply is too much code.

I already mentioned that static analysers heavily rely on PHP's type system in order to gain as much insights in your code as possible. They will even tell you want type information is missing:

```
class Post
{
    public function <hljs striped>title()</hljs> { /* … */ }
}
```

##### Error: Method Post::title() has no return type specified.

On top of that, Psalm and PHPStan extend PHP's type system, in order to allow for much more complex type validation. They do this by adding custom docblock annotations. You can read about them in [Psalm's](https://psalm.dev/docs/annotating_code/supported_annotations/) and [PHPStan's](https://phpstan.org/writing-php-code/phpdoc-types) documentation. They allow for pretty cool functionality, for example to determine whether a string is an actual class name, whether it's a callable, and they even support generics.

We're going to look at many of those features in later emails, no worries!

> On **the matter of docblock types**, I wrote an [in-depth blog post](https://stitcher.io/blog/we-dont-need-runtime-type-checks) on my point of view about PHP's type system. 
> 
> I really like that static analysers extend PHP's type system using docblocks; though ideally, I'd want built-in syntax in PHP that can be used by static analysers and are ignored at runtime. Definitely read [the post](https://stitcher.io/blog/we-dont-need-runtime-type-checks) if you want to know more!

What's unfortunate about sending a newsletter, is that it's impossible to appreciate the power of static analysis within a larger context, a real project. Projects that are worked on for years with several developers truly benefit from as much automation as possible, and I'd say that static analysis cannot be missed in those cases.

Maybe you're working on such a large project right now? Go ahead: play around with Psalm or PHPStan, and let me know what kind of things it detected! Beware though: adding a static analyser in an existing project can feel overwhelming when at first it returns tons of errors. Don't worry, we'll tackle that problem in tomorrow's mail.

Until then!

Brent

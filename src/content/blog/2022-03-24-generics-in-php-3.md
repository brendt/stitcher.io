We’re going to take a deep dive in what’s going on behind the scenes when it comes to generics and PHP. It’s super interesting, and very important to understand why generics aren’t supported yet as first-class citizens in PHP.

Let’s get started.

<div class="sidenote">
<div class="center">
    <a href="https://www.youtube.com/watch?v=BN0L2MBkhNg&list=PL0bgkxUS9EaKyOugEDffRzsvupBE2YEoD&index=3&ab_channel=BrentRoose" target="_blank" rel="noopener noreferrer">
        <img class="small" src="/resources/img/static/generics-thumb-3.png">
        <p><em class="center small">You can watch the video instead of reading a blog post — if you prefer that!</em></p>
    </a>
</div>
</div>

Generics aren’t coming to PHP. That was [Nikita’s conclusion](https://www.reddit.com/r/PHP/comments/j65968/ama_with_the_phpstorm_team_from_jetbrains_on/g7zg9mt/) last year. It simply wasn’t doable.

To understand why Nikita said that, we need to look at how generics could be implemented. In general, there are three possible ways to do so; programming languages that do support generics mostly use one of these three methods.

The first one is called **Monomorphized Generics**. Let’s go back to the first post of this series where I showed this collection example: 

```php
class StringCollection extends Collection
{
    public function offsetGet(<hljs type>mixed</hljs> $key): string 
    { /* … */ }
}

class UserCollection extends Collection
{
    public function offsetGet(<hljs type>mixed</hljs> $key): User 
    { /* … */ }
}
```

I explained that we could manually create implementations of the collection class for each type that we needed a collection for. It would be lots of manual work, there’d be lots of code, but it would work.

Monomorphized generics do exactly this, but in an automated way, behind the scenes. At runtime, PHP would not know about the generic Collection class, but rather about two or more specific implementations:

```php
$users = new <hljs type>Collection</hljs><<hljs generic>User</hljs>>();
// Collection_User

$slugs = new <hljs type>Collection</hljs><<hljs generic>string</hljs>>();
// Collection_strin
```

Monomorphized generics are a totally valid approach. Rust, for example, uses them. One advantage is that there are a bunch of performance gains, because there are no more generic type checks at runtime, it’s all split apart before running the code.

But that immediately brings us to the problem with monomorphized generics in PHP. PHP doesn’t have an explicit compilation step like Rust to split one generic class into several specific implementations; and, on top of that: monomorphized generics do require quite a lot of memory, because you’re making several copies of the same class with a few differences. That might not be as big an issue for a compiled Rust binary, but it is a serious concern for PHP code being run from a central point, the server; maybe serving hundreds or thousands of requests per second.

The next option is **Reified Generics**. This is an implementation where the generic class is kept as-is, and type information is evaluated on the fly, at runtime. C# and Kotlin have reified generics, and it’s the closest to PHP’s current type system, because PHP does all its type checks at runtime. The problem here is that it would require an immense amount of core code refactoring for reified generics to work, and you can imagine some performance overhead creeping in, as we’re doing more and more type checks at runtime.

That brings us to the last option: completely ignore generics at runtime. Act like they are not there; after all, a generic implementation of, for example, a collection class would work with every kind of input anyway.

So if we ignore all generic type checks at runtime, there aren’t any problems.

Well, not so fast. Ignoring generic types at runtime — it’s called **type erasure** by the way, Java and Python do it — it poses some problems for PHP.

For one: PHP not only uses types for validation, it also uses type information to convert values on the fly from one type to another — that’s the type juggling I mentioned in the first post of this series:

```php
function add(<hljs type>int</hljs> $a, <hljs type>int</hljs> $b): int 
{
    return $a + $b;
}

<hljs prop>add</hljs>('1', '2') // 3;
```

If PHP ignored the generic type of this “string” collection, and we’d accidentally add an integer to it, it wouldn’t be able to warn us about that, if the generic type was erased:

```php
$slugs = new <hljs type>Collection</hljs><<hljs generic>string</hljs>>();

$slugs[] = 1; // 1 won't be cast to '1'
```

The second, and more important problem with type erasure — maybe you’re already yelling it at your screen by now — is that the types are gone. Why would we add generic types, if they are erased at runtime?

It makes sense in Java and Pyton, because all type definitions are checked before running the code using a static analyser. Java for example runs a built-in static analyser when compiling code; something that PHP simply doesn’t do: there is no compilation step, and there certainly isn’t a built-in static type checker.

On the other hand… all the advantages of type checking, the ones we discussed in the previous posts; they don’t come from PHP’s built-in runtime typechecker. By the time PHP’s type checker tells us something is wrong, we’re already running the code. A type error essentially crashes our program.

Instead, most of the added value of type checks comes from static analysers that don’t require us to run our code. They are pretty good at making sure there can be no runtime type errors, as long as you, the programmer, provide enough type information. That doesn’t mean there can’t be any bugs in your code, but it is possible to write PHP code that’s completely statically checked and doesn’t produce any type errors while running. And on top of that: there are all the static insights that we get while writing code; that’s by far the most valuable part of any type system, and has nothing to do with runtime type checks.

So do we actually need runtime type checks? Because that’s the main reason why generics can’t be added in PHP today: it’s either too complex or too resource intensive for PHP to validate generic types at runtime.

That’s next time, in the last post of this series.

{{ cta:mail }}

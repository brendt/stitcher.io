This blogpost is based on [this amazing talk by Kevlin Henney](*https://www.youtube.com/watch?v=ZsHMHukIlJY).

Dedicating a whole blogpost to curly brackets might seem like overkill 
but I believe it's worth thinking about them.
Not just because of one curly bracket, but because there's a bigger message in all this. 
Thinking about how we read and write code not only improves the quality of that code,
it also increases our own and others ease of mind when working with it.
It can improve the fluency of your work and free your mind to think about real important stuff.
You know, things like "application logic" for example.

I wrote about visual code improvements a while back in a previous blogpost about [cognitive load](*/blog/a-programmers-cognitive-load).
Today I want to focus on that one little, yet very important character in our codebase: the curly bracket. 
More specifically, we're only going to look at the opening curly bracket, 
because there's little to no discussion about the closing one.

Let's take a look at a code sample.

```php
public function __construct(string $publicDirectory, string $configurationFile, PageParser $pageParser, PageRenderer $pageRenderer) {
    // ...
}
```

A constructor for a render task in Stitcher. It takes two config arguments and two objects.
Depending on the width of your screen, this piece of code might be fully visible in your IDE. 
On this website it surely will not. 

So what's wrong with this code?
Well first of all, you probably have to scroll to read it. That's a bad thing. 
Scrolling requires an extra action for the developer to take. 
You'll have to consciously search for information about the arguments of this method.
That time distracts you from focusing on the application code. 

Second, if you're a web developer, you probably know people don't read, they rather scan.
This is especially true for websites, where the biggest area of attention leans towards the left.
And the same goes for reading code.
Putting important information to the right makes it more difficult to find,
and it also doesn't convey the same importance as things to the left.

In case of an argument list, all arguments are equally important;
yet in the above example a lot of useful information is pushed to that right, dark side.

So how do we pull the useful information more to the left?

```php
public function __construct(string $publicDirectory, 
                            string $configurationFile, 
                            PageParser $pageParser, 
                            PageRenderer $pageRenderer) {
    // ...
}
```

This could be the first thing you think about. But it doesn't really scale.
As soon as you're refactoring a method name, the alignment breaks. 
Say we want to make this a static constructor instead of a normal one.

```php
public static function create(string $publicDirectory, 
                            string $configurationFile, 
                            PageParser $pageParser, 
                            PageRenderer $pageRenderer) {
```

See the alignment breaking? 
Another issue with this approach is that things are still pushed rather far to the right;
let's take a look at another approach.

```php
public function __construct(
    string $publicDirectory, string $configurationFile, 
    PageParser $pageParser, PageRenderer $pageRenderer) {
    // ...
}
```

The advantage here is that the alignment issue on refactoring is solved.
However, how will you decide how many arguments should go on one line? 
Will you make some styling guidelines about this? 
How will you enforce them?
This example has four arguments, but what if it had three or five?  

Consistency is key. If there is a consistent rule about this, you won't have to think about it anymore.
And like we said before, if you don't have to think about this, there's room in your head for more important things.

So let's continue searching for that consistency.

```php
public function __construct(
    string $publicDirectory, 
    string $configurationFile, 
    PageParser $pageParser, 
    PageRenderer $pageRenderer) {
    $this->publicDirectory = rtrim($publicDirectory, '/');
    $this->configurationFile = $configurationFile;
    $this->pageParser = $pageParser;
    $this->pageRenderer = $pageRenderer;
}
```

By giving each argument its own line, we solve the above mentioned problems. 
But there's still one issue with this example: 
it's hard to distinguish between the argument list and the method body.

Kevlin Henney visualises this problem in a simple, yet clever way. 
Let's replace all characters in this code with X's:

```text
XXXXXX XXXXXXXX __XXXXXXXXX(
    XXXXXX XXXXXXXXXXXXXXXX, 
    XXXXXX XXXXXXXXXXXXXXXXXX, 
    XXXXXXXXXX XXXXXXXXXXX, 
    XXXXXXXXXXXX XXXXXXXXXXXXX) {
    XXXXXXXXXXXXXXXXXXXXXX = XXXXXXXXXXXXXXXXXXXXXXXXXXXX;
    XXXXXXXXXXXXXXXXXXXXXXXX = XXXXXXXXXXXXXXXXXX;
    XXXXXXXXXXXXXXXXX = XXXXXXXXXXX;
    XXXXXXXXXXXXXXXXXXX = XXXXXXXXXXXXX;
}
```

Can you see how difficult it has become to spot where the argument list ends 
and the method body starts?

You might say "there's still the curly bracket on the right indicating the end".
That's the thing we want to avoid! We want to keep the visual important information to the **left**.
How do we solve it? Kevlin Henney phrased it very well:

> Turns out, there is one true place where to put your curly brackets - Kevlin Henney

```text
XXXXXX XXXXXXXX __XXXXXXXXX(
    XXXXXX XXXXXXXXXXXXXXXX, 
    XXXXXX XXXXXXXXXXXXXXXXXX, 
    XXXXXXXXXX XXXXXXXXXXX, 
    XXXXXXXXXXXX XXXXXXXXXXXXX
) {
    XXXXXXXXXXXXXXXXXXXXXX = XXXXXXXXXXXXXXXXXXXXXXXXXXXX;
    XXXXXXXXXXXXXXXXXXXXXXXX = XXXXXXXXXXXXXXXXXX;
    XXXXXXXXXXXXXXXXX = XXXXXXXXXXX;
    XXXXXXXXXXXXXXXXXXX = XXXXXXXXXXXXX;
}
```

That is why it makes sense to put that curly bracket on a new line. 
Here's the final result:

```php
public function __construct(
    string $publicDirectory, 
    string $configurationFile, 
    PageParser $pageParser, 
    PageRenderer $pageRenderer
) {
    $this->publicDirectory = rtrim($publicDirectory, '/');
    $this->configurationFile = $configurationFile;
    $this->pageParser = $pageParser;
    $this->pageRenderer = $pageRenderer;
}
```

Now, you might not like this way of structuring your code. 
You might think it adds unnecessary length to a file.
But take a look at the facts:

- You're keeping the important information to the left of the screen, where most of your focus is.
- This method is consistent, which allows us not having to think about it when reading it.
This frees up some of your human "memory space": it reduces cognitive load;
it allows you to focus on the important stuff: the real application logic.
- No one ever died because a file was "longer than absolutely needed". 
People do however get very frustrated working in legacy code bases, 
having to read what other people wrote, especially when that code is difficult to read.
- If the length of the file is really a bother for you, code folding can solve that issue.

I like having this rule when coding. 
There's never a discussion in my head about "should I do it this way or that way"?
This consistency helps me write and read my own code, and benefits other developers too, maybe even years later.

## What about small functions?

Say your function only has one parameter, should it be split on multiple lines?
I personally don't think so. And if we're strictly applying the rules above, 
the curly bracket may be put on the same line.

However, now that we're used to that one almost-empty line between the argument list and the method body,
it does seem like a nice idea to use this visual divider also for smaller functions.

```php
XXXXXX XXXXXXXX __XXXXXXXXX(XXXXXX XXXXXXXXXXXXXXXX) 
{
    XXXXXXXXXXXXXXXXXXXXXX = XXXXXXXXXXXXXXXX;
}
``` 

Now we could start arguing about the placement of that closing bracket, 
but that's a blogpost for another time. 

## And control structures?

The question about `if`, `for`, `while` and others should of course be addressed too.
In my opinion, the answer is simple, we can apply the same rules to them.

If the operands are pushed too far to the right, and we feel the need to split it, we do it like this:

```php
if (
    $firstCondition === $secondCondition
    || $thirdOperand === 1
    || $fourthOperand
) {
    // ...
}
```

Finally, here is a daring thought - and I don't this myself by the way, 
because following standards is also a good thing -
it might make sense to apply the same rule to short control structures.
After all: consistency, right?

```php
foreach ($things as $thing)
{
    // ...
}
```

What do you think? [Let me know]((*https://twitter.com/brendt_gd)).

---

If you're not convinced by now, I'd love to hear why!
You can reach out to me on [Twitter](*https://twitter.com/brendt_gd) or via [e-mail](mailto:brendt@stitcher.io).
I'm looking forward to discussing this further with you!

If you're looking for more to read on clean code. 
Feel free to browse this blog a little further. 
[This is the best starting point](*/blog/a-programmers-cognitive-load).   

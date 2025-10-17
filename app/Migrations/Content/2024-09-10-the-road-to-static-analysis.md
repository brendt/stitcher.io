Over the years, I've focussed on teaching people about modern PHP: I wrote [blog posts](https://stitcher.io/), [newsletters](https://stitcher.io/mail), [a book](https://front-line-php.com/) and made [some podcasts](https://podcasts.apple.com/be/podcast/rant-with-brent/id1462956030), amongst other things.

I've recently came to the conclusion that static analysis is still a rather niche topic within the PHP world, despite it being one of the most promising and exciting things that happened to PHP over the last decade. I want to spend some of my spare time the coming months focussed on teaching people about it. My goal it to get more people excited about static analysis, demystify it a little more, and for it to grow within our wonderful community.  

---

_Static analysis_: you might have heard about it or maybe you've got no clue what it means. Or maybe you do, but are overwhelmed or a bit scared to look into it. In this newsletter series I'll try my best to demystify all questions you might have, and show you how great static analysis works in PHP these days.

What's in it for you, you might wonder?

- You'll deploy less bugs to production
- You'll write clearer and more robust code
- You let the computer be worried about making sure your code works, so you can focus on what actually matters

So, first things first, what _is_ static analysis about? I'll try to come up with the most basic definition possible:

<div class="quote">

**Static analysis** is about letting a program analyse your code for bugs, without actually running that code.
</div>

How's that possible? Let's pretend we're a static analyser for a moment, and look at this function:

```php
function foo(<hljs type>array</hljs> $input): void {}
```

Imagine we'd call this function, but accidentally pass it a string instead of an array:

```php
<hljs prop>foo</hljs>('wrong input');
```

Of course, this function would throw a `<hljs type>TypeError</hljs>` when running it. Though just by _looking_ at it, we could already tell it was wrong: a function that accepts an `<hljs type>array</hljs>`, will not accept a `<hljs type>string</hljs>`.

This is the core principle of any static analyser: looking at type definitions and function calls; and determining whether those operations are valid or not.

It might seem like a simple thing to do, but if you automate this process and scan _all_ your source code; static analysers can actually detect quite a lot of edge cases that you might have missed otherwise.

You do need to pay a small price to enjoy all the benefits that come with static analysis: you'll have to properly use PHP's type system. In fact, most static analysers won't only take PHP's built-in types into account, but will also look at docblocks. The more type information available, the better.

Doesn't that become tedious? Wouldn't it be faster to run your code an see whether it works? On a small scale the answer would be yes, maybe? I hope that you'll be convinced otherwise by the end of this series though.

It's also important to mention that static analysers will try their absolute best to ensure you don't have to write unnecessary code just to make them happy. These are smart programs, and you'll be surprised by what they can do!

So, for the sake of completeness, let's mark our previous example as an error — the same way any static analyser would do:

```php
<hljs prop>foo</hljs>(<hljs striped>'wrong input'</hljs>);
```

##### Error: Argument 1 of foo expects array, "wrong input" provided

And let's continue tomorrow, where we'll look at how to actually get started with static analysers.

See you then!

## A couple of practical things

If you're new to these kinds of [newsletter series](https://road-to-php.com/), I want to get you up to date on a few things: you've subscribed to a separate list that's only used for this series. After the series has ended, you'll be automatically unsubscribed, and will not receive any more emails, ever.

_However_, if you enjoy my content, you can [subscribe to my main newsletter](https://stitcher.io/mail) to receive more updates. I occasionally write about blog posts, personal updates, newsletter series likes these, etc.

Anyway, that's entirely up to you, feel free to wait a couple of days to see whether you find this series helpful.

**One more thing I'd like to ask**: if you enjoy this series, I'd very much appreciate it if you [shared](https://road-to-php.com/static) it with your friends, colleagues, your follower base, on Reddit, HackerNews, or any other place you can think of. Sharing my content really helps me out.

Finally: don't hesitate to hit "reply" and share your thoughts during this series. **I love getting replies**! I always try my best to answer as soon as possible, though it might sometimes take a few days. I'm looking forward to hearing from you!

Brent
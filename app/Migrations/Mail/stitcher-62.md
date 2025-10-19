Today's a good day! [PHP 8.2 is released](https://www.php.net/archive/2022.php#2022-12-08-1)! Now, timezones permitting, you can already install it today, but for me, I'll probably have to wait until tomorrow.

Of course, I've been playing around with PHP 8.2 for months now, but still I look forward to the official release day — today!

I wanted to share a couple of "getting started with PHP 8.2" resources with you as well, I hope you don't mind the self-promotion part that's to come 😅 Here goes:

- **[What's new in PHP](https://aggregate.stitcher.io/links/be2fc4bc-239d-4da6-8627-62da6361b493)** — My big blog post about all things PHP 8.2. If you're reading this newsletter, you're probably already aware that it exists!
- **[My PHP 8.2 video](https://aggregate.stitcher.io/links/f9c4f76c-9ba6-4a2a-a1af-21d2e358abf0)** — If you prefer a video over text.
- **[The Road to PHP 8.2](https://aggregate.stitcher.io/links/eb508cb0-fe44-4881-a56e-939c28fd673e)** — This is my newsletter series about PHP 8.2: if you subscribe you'll receive an email about PHP 8.2 for about a week. More than 3000 people have already subscribed, so you might be interested in it as well 😉
- In case you're running Mac and HomeBrew, here's **[how to update to PHP 8.2](https://aggregate.stitcher.io/links/b84e49a7-ad64-4a00-bd62-9bc243e0bb2b)** — take into account though that it might take a couple of hours (or even days) before Homebrew knows about PHP 8.2!
- And finally, **[PHP 8.2: The Honest Trailer](https://aggregate.stitcher.io/links/108a25af-b111-4885-9b1e-6b86d1495157)** — I just want to point out: this is a joke!  

So, are you looking forward to using PHP 8.2? What are you going to build with it? What's your favourite feature? Let me know! My favourite feature by far are readonly classes. I love using data transfer objects and value objects, and readonly classes just make everything a little more clean. Instead of writing this:

```php
class BlogData
{
    public function __construct(
        <hljs keyword>public readonly</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>public readonly</hljs> <hljs type>Status</hljs> <hljs prop>$status</hljs>,
        <hljs keyword>public readonly</hljs> <hljs type>?Carbon</hljs> <hljs prop>$publishedAt</hljs> = <hljs keyword>null</hljs>,
    ) {}
}
```

We can now write this:

```php
<hljs keyword>readonly</hljs> class BlogData
{
    public function __construct(
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>public</hljs> <hljs type>Status</hljs> <hljs prop>$status</hljs>,
        <hljs keyword>public</hljs> <hljs type>?Carbon</hljs> <hljs prop>$publishedAt</hljs> = <hljs keyword>null</hljs>,
    ) {}
}
```

I love it! 🤩

Oh, one more thing: I made something new as well! I wanted to create a very simplistic graph for following up on [Aggregate's](https://aggregate.stitcher.io/links/25c03a9e-6b6c-42e9-8652-bc9dea197f77) performance. So I ended up building a simple SVG generator that can generate GitHub-styled sparklines, [check it out](https://aggregate.stitcher.io/links/2f36d48f-8f24-42ee-a49a-7c4fd605c3f7)! Here's what it looks like:

<p>
<img src="https://raw.githubusercontent.com/brendt/php-sparkline/main/.github/img/1.png" alt="GitHub styled spark line"></p>

That's about it for today, enjoy PHP 8.2 and let me know when you'll start using it!

Until next time

Brent
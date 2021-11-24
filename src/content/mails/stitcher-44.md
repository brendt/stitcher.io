It's finally here: PHP 8.1 was released today!

Just like previous years, this release is feature-packed and contains a couple of nifty performance improvements as well. If I had to pick my favourite top-three, I think I'd look something like this.

1. [enums](https://stitcher.io/blog/php-enums):

```php
<hljs keyword>enum</hljs> <hljs type>Status</hljs>
{
    case <hljs prop>draft</hljs>;
    case <hljs prop>published</hljs>;
    case <hljs prop>archived</hljs>;
    
    public function color(): string
    {
        return <hljs keyword>match</hljs>($this) 
        {
            <hljs type>Status</hljs>::<hljs prop>draft</hljs> => 'grey',   
            <hljs type>Status</hljs>::<hljs prop>published</hljs> => 'green',   
            <hljs type>Status</hljs>::<hljs prop>archived</hljs> => 'red',   
        };
    }
}
```

2. [being able to use new in initializers](https://stitcher.io/blog/php-81-new-in-initializers):

```php
class PostStateMachine
{
    public function __construct(
        <hljs keyword>private</hljs> <hljs type>State</hljs> <hljs prop>$state</hljs> = <hljs keyword>new</hljs> <hljs type>Draft</hljs>(),
    ) {
    }
}
```

3. [readonly properties](https://stitcher.io/blog/php-81-readonly-properties):

```php
class PostData
{
    public function __construct(
        <hljs keyword>public</hljs> <hljs keyword>readonly</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>public</hljs> <hljs keyword>readonly</hljs> <hljs type>string</hljs> <hljs prop>$author</hljs>,
        <hljs keyword>public</hljs> <hljs keyword>readonly</hljs> <hljs type>string</hljs> <hljs prop>$body</hljs>,
        <hljs keyword>public</hljs> <hljs keyword>readonly</hljs> <hljs type>DateTimeImmutable</hljs> <hljs prop>$createdAt</hljs>,
        <hljs keyword>public</hljs> <hljs keyword>readonly</hljs> <hljs type>PostState</hljs> <hljs prop>$state</hljs>,
    ) {}
}
```

But I should also mention [inheritance cache](https://stitcher.io/blog/new-in-php-81#performance-improvements-pr); while not visible in code, it's a feature that will noticeably improve performance of our PHP 8.1 projects.

There is of course much more to this release, you can read all about it in my [new in PHP 8.1](https://stitcher.io/blog/new-in-php-81) blog post; or subscribe to my [Road to PHP 8.1](https://road-to-php.com/) newsletter to get a daily mail for the coming 10 days, each day covering a new feature in depth.

Suffice to say, I am excited to start using PHP 8.1 in my projects. I'll probably give it a month or two before deploying it to production, but I definitely won't wait much longer. It's been great to see how PHP has grown over the past years, and I find it's a joy to work with day by day.

So what about you? What features are you most excited about? When will you be able to start using PHP 8.1? Let me know by replying to this mail!

<div class="quote">

## In other news

Just in case you're interested in what else I'm working on, there's something I want to share with my **Dutch subscribers**: I've been working on a Dutch podcast about the my first years after graduating: the search for the perfect job, the euphoria when I thought I found it, and then the disappointment, stress and anger when it didn't turn out the way I hoped.

It's a Dutch podcast for now, just because I'm most comfortable telling this story in my own language, but maybe I'll make an English version as well one day. If you're not Dutch then I'm sorry for bothering you with it in this newsletter, but if you are I would _highly_ appreciate you giving it a listen. You can subscribe for updates [here](https://stitcher.io/de-job), or directly listen to it via [Spotify](https://open.spotify.com/show/6rsHpBPovlF3R4KCUD14MU), [Apple Podcasts](https://podcasts.apple.com/us/podcast/de-job/id1596891759) or [Stitcher](https://www.stitcher.com/show/de-job). Thanks!
</div>

With all of that being said, I hope you'll enjoy PHP 8.1 as much as I; until next time!

Brent

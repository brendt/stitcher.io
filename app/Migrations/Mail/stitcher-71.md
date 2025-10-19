It's been exactly one month since I sent my last newsletter. There's of course a very good reason for this longer period of silence: our baby girl, Alina Lore Roose, was born! Despite it being a difficult pregnancy with several complications, Alina is doing great, and we're so happy and blessed to have her in our lives. 

I will keep taking things slow for the coming month though, as I'm only working a couple of days a week. But I am happy being able to send out this newsletter, because I've got quite a lot to share with you after a full month. Let's begin!

## Opinion-driven design

I did manage to make [one video](https://aggregate.stitcher.io/post/e8a560d6-d8d4-440e-a7a4-69b4ae0d375d) over the past month. This one is about how I favor "opinion-driven design". I'm very curious what you think about the topic, but also how you like this story telling format. Will you reply with your thoughts?

<p>
<a href="https://aggregate.stitcher.io/post/e8a560d6-d8d4-440e-a7a4-69b4ae0d375d">
<img src="https://stitcher.io/resources/img/static/opinion-driven-thumb.png" alt="Video Thumbnail" />
</a>
</p>

<p style="text-align: center;">
<a href="https://aggregate.stitcher.io/post/e8a560d6-d8d4-440e-a7a4-69b4ae0d375d">Click to watch</a>
</p>

## Cloning readonly properties

There's a newly accepted RFC that will allow overwriting readonly properties _while_ cloning an object. Beware though: you can only overwrite readonly properties _within_ an object's `<hljs prop>__clone</hljs>` method:

```php
<hljs keyword>readonly</hljs> class Post
{
    public function __construct(
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$title</hljs>,
        <hljs keyword>public</hljs> <hljs type>string</hljs> <hljs prop>$author</hljs>,
        <hljs keyword>public</hljs> <hljs type>DateTime</hljs> <hljs prop>$createdAt</hljs>,
    ) {}
    
    public function __clone()
    {
        $this-><hljs prop>createdAt</hljs> = new <hljs type>DateTime</hljs>(); 
        // This is allowed,
        // even though `createdAt`
        // is a readonly property.
    }
}
```

It's definitely a useful feature for making deep clones of readonly classes, but I also have some side notes to make. I wrote them down in a [dedicated blog post](https://aggregate.stitcher.io/post/efc39d13-4430-4e3c-a571-5860714c1dd1).

## Roundup

There were quite a lot of great posts written during my month off. Here are a couple that stood out:

- **[To Route or To Action - That is the Question](https://aggregate.stitcher.io/post/7901ae88-c4f7-4db3-a956-492b1442f86b)** — Tomas recently discovered the Laravel ecosystem, and is writing a lot of great content about it. 
- **[Valet 4.0 is released](https://aggregate.stitcher.io/post/f80cd3f2-f867-42ce-8494-d7633c3c821a)** — The new major release of Valet is here! There are a couple of new features, but mainly it's internal changes to make debugging easier and make Valet more stable. 
- **[Discovering PHP's first-class callable syntax](https://aggregate.stitcher.io/post/e07e8911-34f7-4668-8d67-1dfb4a2a602c)** — Freek takes a close look at first-class callables. 
- **[The future of Pest v2](https://aggregate.stitcher.io/post/dfef619f-b45f-4343-8f6b-d82a09261bd9)** — Nuno talks about Pest v2, which is actually getting released _today_! Freek also wrote about it [here](https://aggregate.stitcher.io/post/8decb04f-f0a5-48eb-8a25-53fe75c6edd2).
- **[Slashdash](https://aggregate.stitcher.io/post/b6ff1242-3ca7-4c8a-a964-8383bd3e3b24)** — A silly little idea I read about: scope-aware code comments.
- **[PHP Core Roundup #10](https://aggregate.stitcher.io/post/06f8e3c1-ca78-4386-8e17-d282edd2369e)** — What's been happening in PHP Core Land.
- **[Punchcard - Object Configs for Laravel](https://aggregate.stitcher.io/post/02e02c78-7dce-4d05-8938-b8ec42618c7b)** — Based on a blog post of mine, Tomas went ahead and built a package for object-based config files in Laravel. Worth giving a try, in my opinion.
- **[Tech-last](https://aggregate.stitcher.io/post/ec8d6886-c23b-4b9e-a0df-75ac57c94953)** — Robin writes about "new things". His thoughts resonate. 
- Finally, I'd like to mention my [GitHub Sponsors page](https://github.com/sponsors/brendt/) one more time, and thank everyone who's pitching in, you can read about [the story here](https://stitcher.io/blog/sponsors).

--- 

That's it for today's newsletter, I'm happy to be back, but I'm also happy being able to take a slow for a couple more weeks!

Until next time

Brent
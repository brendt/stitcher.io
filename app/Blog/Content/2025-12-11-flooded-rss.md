---
title: I might have just flooded your RSS feed. Sorry!
---

I'm currently merging [Aggregate](http://aggregate.stitcher.io/) into this blog so that everything is managed in the same project. That's all going well, although I might have just made a very annoying mistake that might have caused your RSS reader to fill up with Aggregate's content instead of this blog's content. 

My mistake was that I cached both rendered RSS feeds for an hour, but mistakenly used the same cache key for my blog's feed and Aggregate's feed 🫣 

```php
#[Stateless, Get('/rss')]
public function __invoke(ViewRenderer $viewRenderer, Cache $cache): Response
{
    $xml = $cache->resolve(
        key: 'rss', // 👈 this should have been 'feed-rss'
        callback: fn () => $viewRenderer->render(view(
            __DIR__ . '/feed-rss.view.php',
            posts: Post::published()
                ->orderBy('publicationDate DESC')
                ->limit(50)
                ->all(),
        )),
        expiration: DateTime::now()->plusHours(1),
    );

    return new Ok($xml)->addHeader('Content-Type', 'application/xml;charset=UTF-8');
}
```

The problem was in production for about a minute or two, so I'm not sure whether your RSS reader actually picked up on it or not. If so, then I'm super sorry for the annoyance! Don't hesitate to let me know in [the comments](#comments) or [via email](mailto:brendt@stitcher.io) whether you had any issues or not.
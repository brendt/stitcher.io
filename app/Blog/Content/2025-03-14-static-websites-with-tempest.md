---
title: 'Static websites with Tempest'
disableAds: true
meta:
    description: 'Tempest makes it super convenient to convert any controller action in statically generated pages'
    canonical: 'https://tempestphp.com/blog/static-websites-with-tempest/'
---

_This post was originally published on the [Tempest blog](https://tempestphp.com/blog/static-websites-with-tempest/)._

Let's say you have a controller that shows blog posts — kind of like the page you're reading now:

```php
final readonly class BlogController
{
    #[Get('/blog')]
    public function index(BlogRepository $repository): View
    {
        $posts = $repository->all();

        return view(__DIR__ . '/blog_index.view.php', posts: $posts);
    }

    #[Get('/blog/{slug}')]
    public function show(string $slug, BlogRepository $repository): Response|View
    {
        $post = $repository->find($slug);

        return view(__DIR__ . '/blog_show.view.php', post: $post);
    }
}
```

These type of web pages are abundant: they show content that doesn't change based on the user viewing it — static content. Come to think of it, it's kind of inefficient having to boot a whole PHP framework to render exactly the same HTML over and over again with every request.

However, instead of messing around with complex caches in front of dynamic websites, what if you could mark a controller action as a "static page", and be done? That's exactly what Tempest allows you to do:


```php
use Tempest\Router\StaticPage;

final readonly class BlogController
{
    #[StaticPage]
    #[Get('/blog')]
    public function index(BlogRepository $repository): View
    {
        $posts = $repository->all();

        return view(__DIR__ . '/blog_index.view.php', posts: $posts);
    }

    // …
}
```

And… that's it! Now you only need to run `{console}tempest static:generate`, and Tempest will convert all controller actions marked with `{php}#[StaticPage]` to static HTML pages:

```console
~ tempest static:generate

- <u>/blog</u> > <u>/web/tempestphp.com/public/blog/index.html</u>

<success>Done</success>
```

Hold on though… that's all fine for a page like `/blog`, but what about `/blog/{slug}` where you have multiple variants of the same static page based on the blog post's slug?

Well for static pages that rely on data, you'll have to take one more step: use a data provider to let Tempest know what variants of that page are available:

```php
use Tempest\Router\StaticPage;

final readonly class BlogController
{
    // …
    
    #[StaticPage(BlogDataProvider::class)]
    #[Get('/blog/{slug}')]
    public function show(string $slug, BlogRepository $repository): Response|View
    {
        // …
    }
}
```

The task of such a data provider is to supply Tempest with an array of strings for every variable required on this page. Here's what it looks like:

```php
use Tempest\Router\DataProvider;

final readonly class BlogDataProvider implements DataProvider
{
    public function __construct(
        private BlogRepository $repository,
    ) {}

    public function provide(): Generator
    {
        foreach ($this->repository->all() as $post) {
            yield ['slug' => $post->slug];
        }
    }
}
```

With that in place, let's rerun `{console}tempest static:generate`:

```console
~ tempest static:generate

- <u>/blog</u> > <u>/web/tempestphp.com/public/blog/index.html</u>
- <u>/blog/exit-codes-fallacy</u> > <u>/web/tempestphp.com/public/blog/exit-codes-fallacy/index.html</u>
- <u>/blog/unfair-advantage</u> > <u>/web/tempestphp.com/public/blog/unfair-advantage/index.html</u>
- <u>/blog/alpha-2</u> > <u>/web/tempestphp.com/public/blog/alpha-2/index.html</u>
<comment>…</comment>
- <u>/blog/alpha-5</u> > <u>/web/tempestphp.com/public/blog/alpha-5/index.html</u>
- <u>/blog/static-websites-with-tempest</u> > <u>/web/tempestphp.com/public/blog/static-websites-with-tempest/index.html</u>

<success>Done</success>
```

And we're done! All static pages are now available as static HTML pages that will be served by your webserver directly instead of having to boot Tempest. Also note that tempest generates `index.html` files within directories, so most webservers can serve these static pages directly without any additional server configuration required.

On a final note, you can always clean up the generated HTML files by running `{console}tempest static:clean`:


```console
~ tempest static:clean

- <u>/web/tempestphp.com/public/blog</u> directory removed
- <u>/web/tempestphp.com/public/blog/exit-codes-fallacy</u> directory removed
- <u>/web/tempestphp.com/public/blog/unfair-advantage</u> directory removed
- <u>/web/tempestphp.com/public/blog/alpha-2</u> directory removed
<comment>…</comment>
- <u>/web/tempestphp.com/public/blog/alpha-5</u> directory removed
- <u>/web/tempestphp.com/public/blog/static-websites-with-tempest</u> directory removed

<success>Done</success>
```

It's a pretty cool feature that requires minimal effort, but will have a huge impact on your website's performance. If you want more insights into Tempest's static pages, you can head over to [the docs](/docs/framework/static-pages) to learn more.
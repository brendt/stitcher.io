This blog is over 7 years old now, and I'm still writing on it! Not bad! It has always been a statically generated site, backed by my own static generator. In fact, the name "stitcher" comes from that generator. It's called that way because it "stitches" content together. My goal was to make a super fast website, and I think I succeeded in doing so: it has a score of 100 on lighthouse, both mobile and web, so yeah. I did something right.

Now, my static generator has gone through three complete rewrites over the years. When I started out I dreamt of open sourcing it and it becoming something "big", but now I hope **no one will ever use it**! Over the years I've come to realise I made a lot of things very complicated for no real reason. For example: I wanted to manage everything with YAML — **big mistake**. You can take a look at it yourself, this is part of my site's configuration:

```yaml
/:
    template: blog/overview.twig
    variables:
        title: Blog
        overviewTitle: programming
        posts: src/content/blog.yaml
        meta:
            description: "A blog about modern PHP, the web, and programming in general. Follow my newsletter and YouTube channel as well."
    config:
        order:
            variable: posts
            field: date
            direction: desc

/blog/{id}:
  template: blog/detail.twig
  variables:
    overviewTitle: programming
    post: src/content/blog.yaml
    meta:
      description: "A blog about modern PHP, the web, and programming in general. Follow my newsletter and YouTube channel as well."
  config:
    collection:
      variable: post
      parameter: id
    next: true
    meta: true
```

And this is how I add blog posts (in another YAML file):

```yaml
a-simple-approach-to-static-generation:
  date: 2024-08-30
  title: A simple approach to static site generation
  content: src/content/blog/2024-08-30-a-simple-approach-to-static-generation.md
  disableAds: true

timeline-taxi-chapter-07:
  date: 2024-08-24
  title: "Timeline Taxi: chapter 7"
  content: src/content/taxi/taxi-07.md
  next: timeline-taxi-chapter-01
  disableAds: true

extends-vs-implements:
  date: 2024-08-21
  title: Extend or implement
  content: src/content/blog/2024-08-18-extends-vs-implements.md
  disableAds: true
```

There's so much overhead converting YAML config into something that generates a website, and it's really not worth it. Especially when you realise I need a non-static version for local development. So I actually parse this YAML into a routable application as well 🤢.

Three years ago, I realised that this approach was doing more harm than good, and I tried refactoring stitcher to Laravel: instead of a YAML file, I'd make simple controllers, and somehow generate static pages from those. In other words, I'd use my controllers as "configuration", which had the benefit my content being accessible within a non-static context as well for local development — the controllers were already there.

I actually got pretty far twice, but gave up on it twice as well. It was never a high-stakes project since the original still worked, and I guess other things got in the way.

However, yesterday, I was working on the [Tempest docs website](https://tempestphp.com), and realised it could use a static version as well. Why would I need to boot the framework everytime when all I need is an HTML page? So I did some hacking and… well. I got it working within an hour 😅

Granted, Tempest does most of the heavy lifting, but I dare to say that my experience writing a static generator from scratch three times might have helped as well. Here's what I did.

Tempest already has controller actions — obviously — they look like this:

```php
final readonly class HomeController
{
    #[Get('/')]
    public function home(): View
    {
        return view('home');
    }
}
```

Now, what if we want to generate a static version of this page? We need a way of letting Tempest know it should generate an HTML page from a controller action. That's easy enough using an attribute:

```php
final readonly class HomeController
{
    #[StaticPage]
    #[Get('/')]
    public function home(): View
    {
        return view('home');
    }
}
```

Now, Tempest has a concept called [discovery](https://tempestphp.com/docs/internals/02-discovery), so finding the actions that should be compiled into static pages isn't all that difficult:

```php
final readonly class StaticPageDiscovery implements Discovery
{
    public function __construct(
        private StaticPageConfig $staticPageConfig,
    ) {
    }

    public function discover(ClassReflector $class): void
    {
        // Loop over all public methods
        foreach ($class->getPublicMethods() as $method) {
            // If a method has the `#[StaticPage]` attribute,
            $staticPage = $method->getAttribute(StaticPage::class);

            if (! $staticPage) {
                continue;
            }
            
            // we need to add it to our list of static pages
            $this->staticPageConfig->addHandler($staticPage, $method);
        }
    }

    // Some more boring cache stuff
}
```

So right away, we've got a config file with all the controller actions that should be compiled to static pages. Next, let's create the command that will generate all that content:

```php
final readonly class StaticGenerateCommand
{
    use HasConsole;

    public function __construct(
        private Console $console,
        private StaticPageConfig $staticPageConfig,
    ) {}

    #[ConsoleCommand('static:generate')]
    public function __invoke(): void
    {
        foreach ($this->staticPageConfig->staticPages as $staticPage) {
            // …
        }

        $this->success('Done');
    }
}
```

Right now, nothing much is going on: we inject that static page config which was populated by our discovery class, and we inject the console as well because we want to write some output to it. Now, what should we do with each static page? It holds a reference to a controller action, which we can use to generate a response with, which we can render into HTML. We'll need to inject a couple more framework dependencies to handle the heavy lifting for us, but nothing too complicated:


```php
#[ConsoleCommand('static:generate')]
public function __invoke(): void
{
    $publicPath = path($this->appConfig->root, 'public');
    
    foreach ($this->staticPageConfig->staticPages as $staticPage) {
        // First, we generate the URI for this static page's controller handler
        $uri = uri($staticPage->handler);
       
        // Next, we dispatch a new GET request via Tempest's router 
        $response = $this->router->dispatch(
            new GenericRequest(
                method: Method::GET,
                uri: $uri,
            ),
        );
        
        // We render the response
        $this->viewRenderer->render($response->getBody());

        // And write the HTML to a file
        $file = path($publicPath, $uri . '.html');
        file_put_contents($file, $content);
        
        $this->writeln("- <em>{$uri}</em> > <u>{$file}</u>");
    }

    $this->success('Done');
}
```

So that's all good, but there's an important thing missing: this approach won't work for routes that have dynamic parameters. Take for example Tempest's docs controller action:

```php
final readonly class DocsController
{
    #[StaticPage]
    #[Get('/{category}/{slug}')]
    public function show(string $category, string $slug, ChapterRepository $chapterRepository): View
    {
        return new DocsView(
            chapterRepository: $chapterRepository,
            currentChapter: $chapterRepository->find($category, $slug),
        );
    }
}
```

Yeah, this won't work, since we don't need to render one page, we need to render a page for _every_ `$category` and `$slug` variant. In other words: we need to render a page for every chapter in the docs. It might seem like a difficult problem to solve — it used to be the most difficult thing to get right when I built stitcher — but it's actually pretty trivial. Whenever we have a dynamic route, we'll need a way to specify all variations of that route — something to provide data to fill in the gaps. How about… a data provider?

So, let's make a small change: let's add an interface called `{php}DataProvider`, which has one task… provide data. It's the same concept as PHPUnit's data providers, by the way, it's not too complicated. So let's refactor our docs controller like so:

```php
final readonly class DocsController
{
    #[StaticPage(DocsDataProvider::class)]
    #[Get('/{category}/{slug}')]
    public function show(string $category, string $slug, ChapterRepository $chapterRepository): View
    {
        // …
    }
}
```

And implement that `{php}DocsDataProvider` class next:

```php
final readonly class DocsDataProvider implements DataProvider
{
    public function __construct(
        private ChapterRepository $chapterRepository
    ) {}

    public function provide(): Generator
    {
        foreach ($this->chapterRepository->all() as $chapter) {
            yield [
                'category' => $chapter->category,
                'slug' => $chapter->slug,
            ];
        }
    }
}
```

As you can see, it's not too complicated: we already have a `{php}ChapterRepository` that lists all available chapters, now it's just a matter of providing the right data for every page. So with that in place, we need to make one final change to our generate command, as it needs to take this data provider into account:

```php
#[ConsoleCommand('static:generate')]
public function __invoke(): void
{
    // …
    
    foreach ($this->staticPageConfig->staticPages as $staticPage) {
        // Retrieve the data provider via the container
        $dataProvider = $this->container->get(
            $staticPage->dataProviderClass 
            ?? GenericDataProvider::class
        );

        // Loop over all its iterations
        foreach ($dataProvider->provide() as $params) {
            // Generate the URI for this static page's controller handler
            // WITH the dynamic parameters
            $uri = uri($staticPage->handler, ...$params);
            
            // … The rest stays the same
        }
    }
}
```

And we're done! Now we have one controller action that generates a dynamic amount of pages, all with just a couple of lines of code. I added some null checks and prevented some edge cases — you can check out the [full code here](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Http/src/Static/StaticGenerateCommand.php) if you want to. 

---

In hindsight, I'm really struggling to understand my train of thought for when I built stitcher: why did I make things so complicated, when all I needed was just a couple lines of code? Wisdom comes with age, right? Well, I'm happy with the results, and I believe I'm now able to finally port my blog to something that works a lot easier. All the building blocks are in place, I just need to do the refactor.
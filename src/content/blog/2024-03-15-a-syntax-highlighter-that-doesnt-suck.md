It all started with Aidan sending me this message:

> Huh. Isn't there a better way to do syntax highlighting for that?

He was talking about code blocks for the upcoming [Tempest docs](https://tempest.stitcher.io/) site. I must say I wasn't surprised by his question, although I had hoped he wouldn't ask it. 

I wrote code blocks like so:

<pre>public function store(&lt;hljs type&gt;BookRequest&lt;/hljs&gt; $request)</pre>

That's right, I manually add and parse special "highlight" tokens in them. Take a look at, for example, the source of my latest [what new in PHP 8.3](https://github.com/brendt/stitcher.io/blob/master/src/content/blog/2023-03-17-new-in-php-83.md) post. Lots of `hljs` tags everywhere.

Of course, I don't do this without reason. I used [highlight.js](https://highlightjs.org/) for years, and found it increasingly frustrating that it didn't render code blocks correctly — especially modern PHP syntax. Maybe three or four years ago, I switched to [highlight.php](https://github.com/scrivo/highlight.php), a PHP port of highlight.js. It wasn't any better than highlight.js, but at least it did its rendering server-side, which is always a good thing.

The major benefit of server-side rendering, was that I was able to add a small parser on top of it. With it, I could control my code block's styling when it didn't work exactly right. Hello `hljs` tags!

Maybe it's just me, but I really cannot stand wrongly highlighted code blocks, so I was willing to do whatever it takes to get it right. Even if it meant manual work for every single code block I'd write. It's one of these things I got used to over time though, and I didn't really question it anymore. Shortly after adding my custom syntax, I added keyboard shortcuts to wrap selected text in `hljs` tokens, so it wasn't even that much a bother — to me, at least.

However, when I started drafting the Tempest docs, I knew deep down that this solution wouldn't work long-term. Tempest — and that includes its documentation — is an open source project. If one of the goals is for people to contribute, then I can't expect them to learn a quirky syntax I came up with a couple of years ago. 

So, yes, Aidan's right to ask this question.

> Isn't there a better way to do syntax highlighting for that?

"No problem", I told him, "I'll find a replacement!" Originally I had other plans that day, but I imagined it wouldn't be so hard to switch to another highlighter. It's a couple of years later, surely there will be a better solution now.

Right?

First, I looked at highlight.js again. It was still rendering PHP code as if it was PHP 7.4. Stuff like attributes, function names, and some types weren't highlighted at all. Unacceptable.

Next, I looked at [Torchlight](https://torchlight.dev/). As far as I knew, it's a "highlighter as a service" (it's also a game I used to play, but that's irrelevant). Torchlight was created a year or two ago, and I heard pretty good things of it. I believe the Laravel docs uses it. 

I had high hopes for Torchlight. They had a free plan for open source projects, so let's go!!

Unfortunately, one small issue: it only works in Laravel projects. Torchlight requires Laravel facades to be booted in order to work. That would mean manually booting up Laravel in a project that's not using Laravel at all. On top of that, Torchlight makes API requests, so I figured it wouldn't be the most performant solution anyway. The way I write content is by doing lots of refreshes, and I need changes to be instantly visible. So, Torchlight was out.

Next on my list was [Shiki](https://github.com/shikijs/shiki), which has a [PHP wrapper](https://github.com/spatie/shiki-php). Shiki supports TextMate grammar, which is used for syntax highlighting in many editors. In general, it's  much more accurate than something like highlight.js, _and_ you can run it server-side. Nice!

Remember what I said about performance though? Yeah… Shiki is slow. Like, super slow. It took more than 10 seconds to render the [Controllers docs page](https://tempest.stitcher.io/02-controllers), which only includes a handful of code blocks.

Sure, one "solution" to that is caching, but when I'm writing, I want things to appear instantly. Even with caching, you'd still look at 1-2 second page refreshes when you're making changes to code blocks, which — to me — is unacceptable. 

At this point I was becoming frustrated. Surely it can't be that there is no proper solution to syntax highlighting that satisfies these three requirements??

- Fast enough so that page load times are tolerable without caching during development.
- Server-side rendered, it's just text after all; why would you bother all clients to do more work while the server could do it once?
- Support proper PHP syntax, or at least provide an easy way to add new syntax outside the package's core, so that new PHP syntax (or other languages) will have support immediately when people need it.

"HOW HARD CAN IT BE?" — I asked Aidan in frustration, ready to give up. "We'll stick with my quirky syntax, and I'll manually add `hljs` tags afterward whenever people send PRs. Good enough." 

But Aidan wasn't ready to let go:

> I’m very determined to find a solution to this syntax highlighting problem though.

With these words of "encouragement", I decided to consider one final option. Let's write something myself. It's just syntax highlighting — "how hard can it be?" If I wasn't able to get something working in a couple of hours, I could still abandon the idea. 

"Working on it", I told Aidan.

What happened during the next two days is hard to describe, but you probably recognise the feeling nevertheless: being so deep into "the zone" and determined to solve a problem that everything else must give way.

It took a couple of iterations, some dreaming about it during the night — I tend to dream about code when I'm deep into a project, nothing to worry about. But yesterday morning, everything fell into place.

This is what I sent Aidan at 6:57 AM:

> Ok Aidan, I need you to keep calm. Ok?
> 
> EVERYTHING WORKS 😱😱😱😱😱😱😱😱

Ok, not _everything_ worked, but all important things _did_:

- Server-side rendered highlights
- Fast enough to handle stuff without any caching
- Accurate syntax highlighting
- [An easy API to add new languages](/blog/building-a-custom-language-in-tempest-highlight)
- Language injection support, so that you can combine multiple languages within one codeblock (it's actually pretty neat)

And so… I'd like to present, a code highlighter that doesn't suck: [tempest/highlight](https://github.com/tempestphp/highlight). 

Ok, it still sucks a bit, because I still need to add more languages, and there will probably be some inaccuracies within the languages I already added (PHP, HTML, CSS, and Blade). But: the foundation is there, and it works. I'm actually already using it on the Tempest docs site, because why not?

I'm now going to switch this blog over as well, though I will keep the backwards compatibility option for now, because I don't want to be bothered with updating hundreds of past blog posts. 


So, if you're in need for a code highlighter that doesn't suck, feel free to check out [tempest/highlight](https://github.com/tempestphp/highlight)! Also, if you're looking for an open source project to contribute to: I more than welcome PRs! (The README gives you more info on how to get started.)

PS: give it a star as well while you're there 😉

PPS: here's a code block rendered with the new highlighter:

```php
final readonly class BookController
{
    #[Get(uri: '/books/{book}')]
    public function show(Book $book, User $user): View
    {
        return view('Front/books/detail.view.php',
            book: $book,
            user: $user,
        );
    }
}
```

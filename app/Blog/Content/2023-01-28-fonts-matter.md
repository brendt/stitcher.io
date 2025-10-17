---
title: 'Fonts matter'
disableAds: true
footnotes:
    - { link: /blog/code-folding, title: "I'm a code folder" }
    - { link: /blog/why-curly-brackets-go-on-new-lines, title: 'Why curly brackets go on new lines' }
    - { link: /blog/tabs-are-better, title: 'Tabs are better' }
    - { link: /blog/light-colour-schemes, title: "I'm a light schemer" }
---

<style>
.fonts-01 code {
    font-size: 12px;
    line-height: 1.1em;
    font-family: "Courier New", monospace;
}

.fonts-02 code {
    line-height: 1.1em;
    font-family: "Courier New", monospace;
}

.fonts-03 code {
    line-height: 1.2em;
}
</style>

<p><iframe width="560" height="422" src="https://www.youtube.com/embed/1UxQX00BZug" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></p>

I like to think of my code as a book. Not just any book, I think of it as a precious, beautifully designed work of art. Something I want to _WANT_ to read. You know why? Because programming is so much more about reading and understanding code, than it is about writing.

I would say that “writing code” is only the lesser part of my programming life. So naturally, I have much to gain by making the “reading part” as pleasant as possible.

So, let's work from an example:

<div class="fonts-01">

```php
final class CodeController
{
    public function __construct(<hljs keyword>private</hljs> <hljs type>MarkdownConverter</hljs> <hljs prop>$markdown</hljs>) {}

    public function __invoke(<hljs type>string</hljs> $slug)
    {
        $code = $this-><hljs prop>markdown</hljs>-><hljs prop>convert</hljs>(<hljs prop>file_get_contents</hljs>(<hljs prop>__DIR__</hljs> . "/code/{$slug}.md"))-><hljs prop>getContent</hljs>();

        return <hljs prop>view</hljs>('code', [
            'code' => $code,
        ]);
    }
}
```

</div>

First things first, I choose a large font. My brain can only read so many characters per second, so I don’t need to try and fit as much code as possible on screen, at all times.


<div class="fonts-02">

```php
final class CodeController
{
    public function __construct(<hljs keyword>private</hljs> <hljs type>MarkdownConverter</hljs> <hljs prop>$markdown</hljs>) {}

    public function __invoke(<hljs type>string</hljs> $slug)
    {
        $code = $this-><hljs prop>markdown</hljs>-><hljs prop>convert</hljs>(<hljs prop>file_get_contents</hljs>(<hljs prop>__DIR__</hljs> . "/code/{$slug}.md"))-><hljs prop>getContent</hljs>();

        return <hljs prop>view</hljs>('code', [
            'code' => $code,
        ]);
    }
}
```

</div>

I choose a font that’s pleasant to read, modern fonts suit me better than the ones that originated back in the 80s or 90s.


<div class="fonts-03">

```php
final class CodeController
{
    public function __construct(<hljs keyword>private</hljs> <hljs type>MarkdownConverter</hljs> <hljs prop>$markdown</hljs>) {}

    public function __invoke(<hljs type>string</hljs> $slug)
    {
        $code = $this-><hljs prop>markdown</hljs>-><hljs prop>convert</hljs>(<hljs prop>file_get_contents</hljs>(<hljs prop>__DIR__</hljs> . "/code/{$slug}.md"))-><hljs prop>getContent</hljs>();

        return <hljs prop>view</hljs>('code', [
            'code' => $code,
        ]);
    }
}
```

</div>

I increase the line height, because it gives my code some room to breathe, and makes it even easier to read.


```php
final class CodeController
{
    public function __construct(<hljs keyword>private</hljs> <hljs type>MarkdownConverter</hljs> <hljs prop>$markdown</hljs>) {}

    public function __invoke(<hljs type>string</hljs> $slug)
    {
        $code = $this-><hljs prop>markdown</hljs>-><hljs prop>convert</hljs>(<hljs prop>file_get_contents</hljs>(<hljs prop>__DIR__</hljs> . "/code/{$slug}.md"))-><hljs prop>getContent</hljs>();

        return <hljs prop>view</hljs>('code', [
            'code' => $code,
        ]);
    }
}
```

Finally, I make sure that my code isn’t too wide. The less I need to move my eyes from left to right, the easier it is.


```php
final class CodeController
{
    public function __construct(
        <hljs keyword>private</hljs> <hljs type>MarkdownConverter</hljs> <hljs prop>$markdown</hljs>,
    ) {}

    public function __invoke(<hljs type>string</hljs> $slug)
    {
        $path = <hljs prop>file_get_contents</hljs>(<hljs prop>__DIR__</hljs> . "/code/{$slug}.md");
        
        $code = $this-><hljs prop>markdown</hljs>
            -><hljs prop>convert</hljs>($path)
            -><hljs prop>getContent</hljs>();

        return <hljs prop>view</hljs>('code', [
            'code' => $code,
        ]);
    }
}
```

Looking at typography guidelines, the maximum advised length is somewhere between 60 and 80 characters. I think somewhere between 80 and 100 works well, because code also includes lots of tabs.

Have you considered typography when programming? Give it a try, it’ll make a lasting impression.

<p><iframe width="560" height="422" src="https://www.youtube.com/embed/1UxQX00BZug" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></p>
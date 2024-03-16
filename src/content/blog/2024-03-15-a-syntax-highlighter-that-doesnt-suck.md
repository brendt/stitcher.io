It all started with Aidan sending me this message:

> Huh. There’s not a better way to do syntax highlighting for that?

He was talking about the code blocks for the [upcoming Tempest docs](https://tempest.stitcher.io/). I wasn't surprised by it, since I wrote my code blocks like so:

<pre><code>public function store(&lt;hljs type&gt;BookRequest&lt;/hljs&gt; $request)</code></pre>

That's right, I manually add and parse special "highlight" tokens in my code blocks. Take a look at, for example, the source of my latest [what new in PHP 8.3](https://github.com/brendt/stitcher.io/blob/master/src/content/blog/2023-03-17-new-in-php-83.md). Lots of `hljs` tags everywhere.

Of course, I don't do this without reason. I used [highlight.js](https://highlightjs.org/) for years, and found it increasingly frustrating that it didn't render code blocks correctly — especially modern PHP syntax. The problem became worse when I switched to [highlight.php](https://github.com/scrivo/highlight.php), a PHP port of highlight.js that isn't really actively maintained. But at least, I was now rendering code block highlighting server-side, which was a good thing.

Maybe it's just me, but I really cannot stand badly highlighted code blocks. So once I switched to highlight.php, I added a small parser on top of it. With this parser, I could control my code block's styling when it didn't work exactly right. Hello `hljs` tags!

It's one of these things I got used to over time, and didn't really question anymore. I've got keyboard shortcuts to wrap selected text in my special `hljs` tokens, so it's not that much a bother — to me, at least.

But when I started drafting the Tempest docs, I knew deep down that this solution wouldn't work long-term. Tempest (including its docs) is an open source project. If one of the goals is for people to contribute, then I can't expect them to learn a quirky syntax I came up with a couple of years ago. 

So yes, Aidan's right to ask this question.

> There’s not a better way to do syntax highlighting for that?

"No problem", I told him, "I'll find a replacement!"
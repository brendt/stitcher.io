We're going to talk about Phan for a moment. Phan is a little different compared to Psalm and PHPStan, which is why I didn't give it much attention during the previous mails.

While Psalm and PHPStan focus on proving correctness, Phan does the opposite. Here's a little excerpt from Phan's [GitHub page description](https://github.com/phan/phan): 

<div class="quote">

Phan prefers to avoid false-positives and attempts to prove incorrectness rather than correctness.
</div>

But what does that mean?

Well, it's important to realise why Phan was created in the first place: Rasmus Lerdorf — the creator of PHP — created this tool specifically for their enormous codebase at Etsy. As we've seen in a previous email, using a static analyser in an existing code base differs a lot from using it from day 1. There are ways to cope with existing project errors by ignoring them and setting a baseline, but still Phan wanted to shift its focus more to just finding actual bugs.

Does that make it less useful? Definitely not. It's maybe even easier to start using Phan in existing projects because of this characteristic. On top of that, there's Phan's speed. It's ideally used in combination with the [php-ast extension](https://github.com/nikic/php-ast#installation). Note that there's also the [PHP-Parser package](https://github.com/nikic/PHP-Parser), which is a similar implementation written in plain PHP. In fact, both Psalm and PHPStan use the latter; while Phan recommends using the extension.

All it requires is a `pecl install php-ast` by the way, there's nothing really complex to it, _but_ it does offer the benefit of making Phan x-times faster than Psalm or PHPStan. This can be beneficial for large code bases.

From my limited experience with Phan, I find that its major downside though is the lack of tooling. Where Psalm and PHPStan offer a proper CLI integration, Phan basically tells you to use UNIX commands to build a report for yourself:

```txt
phan --progress-bar -o analysis.txt

cat analysis.txt \
    | cut -d ' ' -f2 \
    | sort \
    | uniq -c \
    | sort -n -r
```

##### Phan's very basic tooling

I must admit that being used to Psalm and PHPStan, this bare-bones approach was a little off-putting.

There are some proponents of Phan though, claiming it has better integration with PHP extensions and doesn't bother developers with false positives — I can see merit in those.

Yet, what I think is lacking the most is a proper community: there's no Twitter account for Phan or dedicated website. It kind of lives on its own in its GitHub repository, while Psalm and PHPStan are much more _out there_. That doesn't say anything about the tool itself, though a small community often is a limiting factor in its growth.

So: Phan? I don't know. I have played around with it a little bit and was surprised by its speed and indeed noticed less false positives. Is that enough though?

I'll let you decide. You can take a look at the [getting started in a large and sloppy codebase](https://github.com/phan/phan/wiki/Tutorial-for-Analyzing-a-Large-Sloppy-Code-Base) article, which guides you step-by-step through setting up and running Phan. Let me know what you think of it!

With all of that being said, I've got one more "see you tomorrow" left for you — because tomorrow is indeed the very last mail of this series.

See you then!

Brent

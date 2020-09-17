<div class="author footnotes">

This is a mail I sent to PHP's internals, these are my thoughts, and you can follow the internals discussion [here](https://externals.io/message/111875), or share your own thoughts [on Reddit](https://www.reddit.com/r/PHP/comments/iuhtgd/ive_proposed_an_approach_to_generics_on_internals/?).

</div>

Hello internals

Today I'd like to hear your thoughts on what might be a controversial topic, though I think it's worth having this discussion. I want to make the case for adding generic syntax, without actually enforcing any additional type checks at runtime. Please hear me out.

We've been discussing generics for years now [1][2], all without any result. Nikita's latest attempt [3] stalled because, from what I gathered and amongst other things, doing generic type checks at runtime has a significant impact on performance.

On the other hand, static analysers have been making their rise for a few years now. Granted: not the whole community might like this kind of type strictness, and PHP doesn't force them to; but still projects like PhpStorm acknowledge their significance — they will add built-in support for both psalm and PHPStan later this year [4]. Rasmus Lerdorf also showed interest in the idea of improving PHP's static analysis capabilities two years ago [5].

That all to say that there's a significant part of the PHP community who's interested in embracing the benefits of static analysis. 

If we look outside of our PHP bubble, we can see the same thing happening in JavaScript: the core benefit that TypeScript adds is its robust static analysis. Sure those developers need an extra compilation step to transpile their code to plain old JavaScript, but it seems that they are… fine with that?

I'd like to discuss a similar idea for PHP. If runtime generics aren't possible because of performance issues, why not explore the other option: adding generic syntax that is ignored by the interpreter, but can be used by static analysis tools — third party of built-into PHP, that's another discussion. I realise this thought goes against the "PHP mindset" we've been programming with for more than 20 years, but I think we shouldn't ignore what's happening in the PHP- and wider programming community: static analysis is relevant, whether you want to use it or not, and a stricter type system is preferred by many.

Now I know there are alternatives we can use today. Static analysers already support generics, using doc blocks. I'm not trying to argue that it's impossible to achieve the same results with the toolset we have, but rather that there's room for improvement from the developer's point of view. History has shown that such convenience additions to PHP have been a difficult pill to swallow for some, but on the other hand those kind of changes _have_ been happening more and more often anyway: property promotion, short closures, named arguments, attributes, yes even types themselves: you can write the same working PHP program without any of those features, and yet they have been proven so useful and wanted over the last years.

As a sidenote: the idea of transpiling is already present in PHP. Looking at constructor property promotion: a purely syntactical feature, which is transformed to simpler PHP code at runtime. Nikita called this principle "desugaring" in the constructor property promotion RFC [6].

So here's my case for transpiled generics summarized:

- There's no significant runtime performance impact
- The PHP community is already embracing static analysis
- Transpiling has been proved to be a viable workflow, thanks to TypeScript
- As with all things-PHP: it's opt-in. You don't have to use the syntax if you don't want to and you won't experience any downsides

So with all that being said, I'm looking forward to hearing your thoughts. 

Kind regards<br>
Brent

- [1] [https://wiki.php.net/rfc/generics](https://wiki.php.net/rfc/generics)
- [2] [https://wiki.php.net/rfc/generic-arrays](https://wiki.php.net/rfc/generic-arrays)
- [3] [https://github.com/PHPGenerics/php-generics-rfc/issues/45](https://github.com/PHPGenerics/php-generics-rfc/issues/45)
- [4] [https://blog.jetbrains.com/phpstorm/2020/07/phpstan-and-psalm-support-coming-to-phpstorm/](https://blog.jetbrains.com/phpstorm/2020/07/phpstan-and-psalm-support-coming-to-phpstorm/)
- [5] [https://externals.io/message/101477#101592](https://externals.io/message/101477#101592)
- [6] [https://wiki.php.net/rfc/constructor_promotion#desugaring](https://wiki.php.net/rfc/constructor_promotion#desugaring)

Today's mail is about a Psalm-specific feature: taint analysis. That sounds like a difficult word, but all it means is that Psalm is able to scan your codebase for security vulnerabilities like XSS or SQL injections. 

That's a difficult task to get right though: a "taint source" — the origin of a vulnerability — can live deep down in a framework or external library, and Psalm needs to build a graph of all method calls in order to determine possible security leaks.

On the other hand, security vulnerabilities can often be much more dangerous than type errors: type errors most likely result in a crash somewhere, while a security issue is often much more subtle. Matthew Brown, the creator of Psalm, [phrased it](https://psalm.dev/articles/detect-security-vulnerabilities-with-psalm) like this:

<div class="quote">

While a null-pointer error can make itself known very quickly, you can execute code for a decade without noticing it has a serious [security] vulnerability.
</div>

Taint analysis to the rescue: if we're already scanning our code for type errors, why not scan it for these kinds of issues as well. There is of course a performance cost to doing this, which is why running the taint analyser is a separate option in Psalm:

```
./vendor/bin/psalm --taint-analysis
```

It's perhaps best to only run this analysis once before deployment, or in a CI environment — we'll talk about these things in a later email.

Of course there are options and annotations to mark functions as safe or unsafe, as well as different types of taint. You can read all about it in [Psalm's documentation](https://psalm.dev/docs/security_analysis/).

I find taint analysis to be a good example of how far static analysis can go in order to help developers build qualitative and safe code. I want to avoid copy/pasting existing documentation, so this email is a shorter one; but I still hope you found it helpful to learn about the extended possibilities of static analysers. Tomorrow, we'll look at another cool feature.

See you then!

Brent

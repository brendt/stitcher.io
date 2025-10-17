---
title: 'Rational thinking'
---

Now and then, I like to ask a simple coding style question on Twitter. It usually goes something like this: do you write FQCN's (fully qualified class names) in your doc blocks or not? In other words, do you write code like this:

```php
/** @var <hljs type>\App\Models\Foo[]</hljs> */
$arrayOfFoo = …
```

Or like this:

```php
/** @var <hljs type>Foo[]</hljs> */
$arrayOfFoo = …
```

Of course, the second example assumes you've imported the full class name at the top of your file.

```php
use <hljs type>\App\Models\Foo</hljs>;

// …

/** @var <hljs type>Foo[]</hljs> */
$arrayOfFoo = …
```

I've asked this question maybe three or four times over the years, and I consistently get the same answer from a large group of respondents: they use the full class name, so that they don't have to scroll to the top of the file to know what they are dealing with.

My response, time and time again, has been: so what about real types? Property types, argument types, return types? Do you use the full class name in those cases as well? 

```php
class Bar
{
    public function baz(<hljs type>\App\Models\Foo</hljs> $foo): <hljs type>\App\Models\Foo</hljs>
    {
        // …
    }
}
```

I've actually had one person say "yes" to that question, and fair enough, they are consistent. But all the others say they don't. They write it like this:

```php
use <hljs type>\App\Models\Foo</hljs>;

class Bar
{
    public function baz(<hljs type>Foo</hljs> $foo): Foo 
    {
        // …
    }
}
```

So what's the difference between doc block types (which are required in some cases because PHP's type system is limited), or real types? Why do you want to import one, but not the other; and how does "not scrolling to the top" make a good argument when it isn't consistent?

---

Programmers often take pride in their rational thinking. We look at problems from a slightly different angle than non-programmers do. It's probably this personality trait that got many people into programming to begin with.

I don't mind that people aren't consistent in how they write types in or out of doc blocks. But I _am_ always surprised with how difficult it is to defend that opinion, once you start asking deeper questions, once people are forced to think about it a little more.

It makes me wonder, could it be that there are more such opinions that we think we're sure of; but that, in reality, we haven't actually thought through all that well? Tabs or spaces, light or dark colour schemes, active record or entity mapper, dependency injection or service location, … 

How many "rational opinions" do we have that turn out to be irrational after all? Opinions that are habit-driven; that we think of as "the best option" — not because they are, but because they've worked in the past and we're comfortable using them.

Are we — the "rational thinkers" — in fact, just like everyone else, influenced by emotion, sometimes without even knowing it?

---

The real reason to always use full class names in doc blocks, by the way, is because PHP doesn't have a reflection API for import statements. So if you want to map a class name from a doc block to its FQCN, you'd need to manually parse that PHP file. Mind you: import statements are quite difficult to parse: there are aliases, partial namespace imports, grouped imports, function imports, and more. 

Fortunately, the problem I describe here isn't really a problem anymore. There's a package called `phpdocumentor/type-resolver` that supports exactly [this kind of parsing](https://github.com/phpDocumentor/TypeResolver#resolving-partial-classes-and-structural-element-names).

So the only _real_ argument against importing doc block types, turns out to be not so relevant anymore.

---

How sure are we of our opinions? How fiercely and emotionally do we defend those opinions, even when we haven't thought them through all that well? And can we admit it when we're wrong, maybe apologise and move on?

I don't want to start a fight over tabs or spaces, importing types or not; but I do want to encourage you to critically look at your own opinions. Wonder whether you thought them through well enough; and if you'd be willing to change them, if they turn out to be more biased than you thought.

---

Are you angry right now? Do you want to tell me I'm wrong? Send me [an email](mailto:brendt@stitcher.io) or [a tweet](https://twitter.com/brendt_gd) and we can have a proper internet fight! No really, I would very much appreciate you challenging these thoughts if you don't agree, I'm looking forward to hearing from you!

Oh and if you want to stay up-to-date about my content and these kinds of posts, consider subscribing to [my newsletter](/newsletter/subscribe).

{{ cta:like }}

{{ cta:mail }}

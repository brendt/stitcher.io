[PHP 8.1](/blog/new-in-php-81) is already taking shape quite well, yet there's one feature I'd love to see added, that's still being discussed: multi-line short closures. The [RFC](*https://wiki.php.net/rfc/auto-capture-closure) calls them "Auto-capturing multi-statement closures" but I hope you don't mind me using the somewhat shorter name.

If you're on an [actively supported](*https://www.php.net/supported-versions.php) PHP version, you already know about [short closures](/blog/short-closures-in-php) — a.k.a. "arrow functions". And, most importantly, one of their biggest drawbacks: they only [support one-line expressions](/blog/short-closures-in-php#no-multi-line), which are also used as the return value.

The multi-line short closures RFC by [Nuno](*https://twitter.com/enunomaduro) and [Larry](*https://twitter.com/crell) aims to solve that problem, in — what I think is — an elegant way.

Some people are skeptical about this RFC though, and I want to address their arguments and share why I think it's still a worthwhile addition. Though first, I'll show you what the RFC is about. Keep in mind this is a proposal, and not added to PHP yet!  

{{ ad:carbon }}

A quick refresher, this is what arrow functions in PHP look like today: 

```php
$getTitle = <hljs keyword>fn</hljs> (<hljs type>Post</hljs> $post) => $post-><hljs prop>title</hljs>;
```

And this is what the RFC proposes:

```php
$date = <hljs prop>now</hljs>();

$getSlug = <hljs keyword>fn</hljs> (<hljs type>Post</hljs> $post) {
    $slug = <hljs type>Str</hljs>::<hljs prop>slug</hljs>($post-><hljs prop>title</hljs>);
    
    return $date . $slug;
}
```

There are two important things to note about multi-line short closures:

- they have access to the variables in the upper scope without needing `<hljs keyword>use</hljs>`; and
- they _don't_ automatically return the last expression, which makes sense since there can be several expressions in a multi-line closure.

You might have already noticed it, but the RFC introduces an elegant kind of symmetry in how you can create closures:

- on the one hand there's `<hljs keyword>function</hljs>` and `<hljs keyword>fn</hljs>`, the first one doesn't auto-capture variables from the outside, the second one does; and
- on the other hand there's `{ … }` or `=>`, using curly brackets allow you to write multiple lines, while `=>` only accepts one expression, but also returns it.

Because of this symmetry, all of the following code samples are possible and all of them behaving a little differently.

Here's a closure that doesn't auto-capture outer scope, with multiple lines:

```php
$date = <hljs prop>now</hljs>();

$getSlug = function (<hljs type>Post</hljs> $post) use ($date) {
    $slug = <hljs type>Str</hljs>::<hljs prop>slug</hljs>($post-><hljs prop>title</hljs>);
    
    return $date . $slug;
}
```

Next, a closure that does capture outer scope and immediately returns the result:

```php
$date = <hljs prop>now</hljs>();

$getSlug = <hljs keyword>fn</hljs> (<hljs type>Post</hljs> $post) => $date . <hljs type>Str</hljs>::<hljs prop>slug</hljs>($post-><hljs prop>title</hljs>);
```

And finally — proposed by the RFC — a closure that does capture outer scope, but still allows multiple lines:

```php
$date = <hljs prop>now</hljs>();

$getSlug = <hljs keyword>fn</hljs> (<hljs type>Post</hljs> $post) {
    $slug = <hljs type>Str</hljs>::<hljs prop>slug</hljs>($post-><hljs prop>title</hljs>);
    
    return $date . $slug;
}
```

If you're counting, you know that one option is still missing: a closure that doesn't auto-capture outer scope and immediately returns a value:

```txt
$date = <hljs prop>now</hljs>();

$getSlug = <hljs keyword>function</hljs> (<hljs type>Post</hljs> $post) <hljs keyword>use</hljs> ($date) 
    => $date . <hljs type>Str</hljs>::<hljs prop>slug</hljs>($post-><hljs prop>title</hljs>);
```

The RFC lists this last one as a possible future addition, but doesn't cover it right now.

---

With the background info out of the way, let's look at some counter arguments as to why some people don't like this change.

### Auto-capturing outer scope can lead to bugs

Some people voice their fear about auto-capturing variables from the outer scope, especially that it can lead to unclear code in the long run and thus, bugs.

I've got a few arguments against that fear.

First, auto-capturing is already supported by PHP in the current short closures, there's nothing about this RFC that changes that. The arrow function RFC passed with 51 votes for, and 8 against after a [thorough discussion](*https://externals.io/message/104693#104693), and has been used extensively in lots of projects since — take a look at some popular OSS frameworks if you want some examples. Clear signs that this particular behaviour is wanted by the majority of people, whether you're afraid of it or not.

Nikita also shares this opinion:

> I'm generally in favor of supporting auto-capture for multi-line closures. I think that extensive experience in other programming languages has shown that auto-capture does not hinder readability of code, and I don't see any particular reason why the effects in PHP would be different. — [https://externals.io/message/113740#114239](*https://externals.io/message/113740#114239)

Auto-capturing outer scope might not be your preferred way of coding, but that doesn't mean the idea should be dismissed, as there are many people who _do_ prefer this style.

Fear should never be the driving force in a programming language's design, we should look at facts instead.

{{ cta:mail }}

### By-value or by-reference passing

I believe the RFC gets it right when it says that outer-scope variables are always captured by-value, and not by-reference. This means that you won't be able to change the value of an outer-scope variable from within your closure — a good thing.

People might argue that this will cause confusion, because what happens if you _do_ want to change the outer-scope variable? We could discuss about whether this would ever be a good idea or not, but it doesn't even matter since PHP already has the answer — remember that symmetry I talked about earlier?

If you want by-reference passing, you'll need to specifically say which variables you want access to — which is exactly what `<hljs keyword>function</hljs>` allows:

```txt
$date = <hljs prop>now</hljs>();

$getSlug = <hljs keyword>function</hljs> (<hljs type>Post</hljs> $post) <hljs keyword>use</hljs> (&$date) {
    $date = <hljs prop>now</hljs>()-><hljs prop>addDay</hljs>();
    
    <hljs comment>// Please don't do this…</hljs>
}
```

I'd argue that this RFC creates clear boundaries between what's possible with `<hljs keyword>function</hljs>` and `<hljs keyword>fn</hljs>`. It doesn't cause confusion; on the contrary: it creates consistency and clarity within PHP's syntax.

### There's little space to be gained

Some people argue that there's no need for adding multi-line short closures because there's little to be gained when it comes to the amount of characters you're writing. That might be true if you're not relying on outer-scope values, but to be honest it's not about how many characters you write. 

This RFC makes the language's syntax more consistent, and a consistent language allows for easier programming.

When writing code, I don't want to be bothered with having to change `<hljs keyword>fn</hljs>` to `<hljs keyword>function</hljs>` when refactoring a closure that suddenly does need to be written on two lines instead of the prior one. 

It might seem like such a small detail, but it are those details that impact our day-by-day developer life. And isn't that what a maturing language should be about? Look at features like property promotion, named arguments, enums, attributes, the match operator, etc. You could argue that none of those feature are "necessary" to write working PHP code, but still they do improve my day-by-day life significantly — and I hope yours too.

### Holding on to the past

Finally, some people might find it difficult to deal with change, and you really need to ask yourself that question if you're voting on RFCs. Yes, you might not see a need for a given feature but you're not only voting for yourself, you have a responsibility to the PHP community; there's more to this than just your projects and your team.

Do you know why closures right now don't auto-import variables from the outer scope? You might have gotten used to it, but do you know why they were designed this way 12 years ago? Larry [did some digging](*https://externals.io/message/113740#113780) in the mailing list archives, and discovered there were three reasons why `<hljs keyword>use</hljs>` was introduced in the first place:

- there were performance concerns if variables of the outer-scope were auto-captured — concerns that are not longer relevant today;
- it was used to avoid surprise by-reference value passing — which also isn't a problem since we're always using by-value passing; and
- it allowed users to explicitly capture variables by-value or by-reference, which is now cleanly solved because of the distinction between `<hljs keyword>function</hljs>` and `<hljs keyword>fn</hljs>`.

You might have gotten used to closures not auto-importing variables for the past decade, but keep in mind this behaviour was only added as a necessity back in the day. All arguments for only using explicit capture have been nullified by time, a great sign that PHP is maturing even more.

{{ cta:mail }}

With all of that being said, I'm looking forward to Nuno and Larry opening the vote on their RFC. PHP 8.1's feature freeze is planned for the 20th of July, 2021; so there's still some time to finalize the details. If you're voting on RFCs, I truly hope you can see the big picture, as this is one of the RFCs that will have a significant impact on many people's developer life.	

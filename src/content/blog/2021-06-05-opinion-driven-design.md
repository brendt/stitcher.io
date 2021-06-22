I once worked at a company that wrote and maintained an in-house framework. Over the course of ten years, they probably
made around 250 websites and applications with it. Despite many shortcomings, the company kept using their framework for
a simple reason: they were in control.

Thanks to that control, they were able to tailor their framework to their own needs without any overhead. And, while I
would argue that using popular, community-backed frameworks instead of writing your own is almost always the better
choice, I can appreciate some of their reasoning.

Except, in the end, they utterly failed in creating what they originally set out to do. Instead a tool shaped
specifically for their needs, the framework's core developers often wanted flexibility: they dreamt of open sourcing
their framework and it growing popular, so it needed to handle as many cases as possible to reach as wide an audience as
possible. And thus, flexibility and configuration were often prioritized, even though they rarely added much — if any —
value to company projects. The core developers were always able to convince the not-so-technical managers; while in
reality, the design and flexibility of this framework was often just a burden for me and my colleagues to deal with.

Problems like premature optimizations and over-abstractions are unfortunately common in software design. I'm not sure
why, but somehow many programmers tend to think they need to account for every possible outcome, even when those
outcomes aren't relevant to their use cases. There often is some kind of fear for losing our audience when our solutions
can't handle the most specific of specific edge cases. But the opposite is true.

---

Lately I've come to appreciate an opinion-driven approach to software design. Especially in
the [open source](*https://spatie.be/open-source?search=&sort=-downloads) world, where you're writing code for others to
use. I often tricked myself in thinking I'd need to write more code for more flexibility. "If I want this package to be
useful to many" — I said to myself — "I need it to be flexible".

I've stopped telling myself those lies.

Instead, I prefer one way of solving a problem, instead of several possibilities. It's my goal to come up with a
solution that is extremely good and user friendly, but still only offers one way of doing something.

As an open source maintainer, I realise not everyone might like my solution as much as I do. Others might have preferred
a solution that slightly better fit their problem. In the end though, if the job gets done, if my code is reliable,
clear and useful, there rarely are any complaints. I started to prefer opinion-driven design when I realised that
flexibility comes with a price that is often not worth paying.

I'm not the only one benefiting by the way. When users of my open source code only get one way of doing something, they
don't have to be worried about micro-decisions that don't affect the end result. And that, for me, is good software
design: allowing programmers to focus on decisions that really matter and offer value to their projects or clients,
instead of wasting time on unnecessary details.

---

Interested in some examples? I've been maintaining
a [data-transfer-object package for PHP](*https://github.com/spatie/data-transfer-object) for a couple of years now; and
recently we tagged a new major version.

The most significant change with version 3 of the package was that type validation was completely reworked. Instead of
manually parsing doc blocks and validating types, we only allow PHP's built-in types anymore. Thanks
to [PHP 8](*/blog/new-in-php-8), almost all use cases can actually be covered with built-in types.

However, we do lose _some_ functionality: typing "arrays of …" becomes a little more tedious, at least if you want the
package to have runtime type validation on those. On the other hand, we realised that static type checkers for PHP are
only growing in popularity, so we made the bold decision to simply not support runtime type validation any more.

Although it's a very opinionated one, we didn't go lightly over this decision: it took
almost [half a year](*https://github.com/spatie/data-transfer-object/issues/151) to get everything right. But in the end
we made the right choice. We took a strong stance for static type systems, and reimagined the package from the ground
up, and thus made it future-proof.

I've got another example from Laravel, the popular back-end framework. I work with it every day and absolutely love it.
And still there is room for improvement.

Take a look at request validation for example:

```php
class MyRequest extends Request
{
    public function rules(): array
    {
        return [
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',
        ];
    }
}
```

According to [the docs](*https://laravel.com/docs/8.x/validation#creating-form-requests), request rules are written as a
string, split by the `|` operator.

However, you can also write them as an array:

```php
class MyRequest extends Request
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'unique:posts', 'max:255'],
            'body' => 'required',
        ];
    }
}
```

You can also represent some rules using objects:

```php
class MyRequest extends Request
{
    public function rules(): array
    {
        return [
            'title' => [new <hljs type>Uppercase</hljs>, 'required', 'unique:posts', 'max:255'],
            'body' => 'required',
        ];
    }
}
```

And use closures as well:

```php
class MyRequest extends Request
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'unique:posts', 'max:255'],
            'body' => [
                function ($attribute, $value, $fail) 
                {
                    if ($value === 'foo') {
                        $fail('The '.$attribute.' is invalid.');
                    }
                },
            ],
        ];
    }
}
```

And this leaves me wondering… why do we need four ways of doing the same thing? Of course, the answer isn't simple if you're considering a framework with ten years of history. These features were added incrementally over the years, and the old ways were kept for backwards compatibility.

Can there be a moment though where these old ties are broken? Where opinionated decisions can be made to improve such code bases, as well as the developer experience of people using them?

I'd like to see some more opinion-driven design in the software world. I believe it would result in better software, overall.

{{ cta:diary }}

I once worked at a company that wrote and maintained an in-house framework. Over the course of ten years, they probably
made around 250 websites and applications with it. Despite many shortcomings, the company kept using their framework for
a simple reason: they were in control.

Thanks to that control, they were able to tailor their framework to their own needs without any overhead. And, while I
would argue that using popular, community-backed frameworks is almost always the better
choice over writing your own , I can appreciate some of their reasoning.

{{ ad:carbon }}

Except, in the end, they utterly failed in creating what they originally set out to do. Instead of a tool shaped
specifically for their needs, the framework's core developers often wanted flexibility: they dreamt of open sourcing
their framework and it growing popular, so it needed to handle as many cases as possible to reach as wide an audience as
possible. And thus, flexibility and configuration were often prioritized, even though they rarely added much — if any —
value to company projects. The core developers were always able to convince the not-so-technical managers; while in
reality, the design and flexibility of this framework was often just a burden for me and my colleagues to deal with.

This mindset of "high configurability and flexibility" is, unfortunately, common in software design. I'm not sure
why, but somehow programmers — myself included — often think they need to account for every possible outcome, even when those
outcomes aren't relevant to their use cases. Many of us deal with some kind of fear of losing our audience if the code we're writing isn't able to handle the most specific of specific edge cases. A very counter-productive thought.

---

Lately I've come to appreciate an opinion-driven approach to software design. Especially in the [open source](*https://spatie.be/open-source?search=&sort=-downloads) world, where you're writing code for others to use. I used to tell myself I'd need to write more code for more flexibility "if I want this package to grow popular".

I don't believe that anymore.

These days, I prefer one way of solving a problem, instead of offering several options. As an open source maintainer, I realise that not everyone might like the solutions I come up with as much as I do; but in the end, if the job gets done, if my code is reliable, clear and useful; there rarely are any complaints. So I started to prefer opinion-driven design when I realised that flexibility comes with a price that is often not worth paying.

I'm not the only one benefiting by the way. When users of my open source code only get one way of doing something, they
don't have to be worried about micro-decisions that wouldn't affect the end result. And that, for me, is good software
design: allowing programmers to focus on decisions that really matter and offer value to their projects and clients;
instead of wasting time on unnecessary details.

---

Are you wondering about examples? I've been maintaining
a [data-transfer-object package for PHP](*https://github.com/spatie/data-transfer-object) for a couple of years now; and
recently tagged a new major version.

The most significant change with version 3 of the package was that type validation was completely reworked. Instead of
manually parsing doc blocks and validating types at runtime, we only allow PHP's built-in types anymore. Thanks
to [PHP 8](*/blog/new-in-php-8), almost all use cases can actually be covered with those built-in types.

However, we do lose _some_ functionality: typing "arrays of …" becomes a little more tedious, at least if you want the
package to have runtime type validation on those. On the other hand, we acknowledged that static type checkers for PHP are
only growing in popularity, so we made the bold decision to simply not support runtime type validation any more.

Although it's a very opinionated decision, we didn't go lightly over it: it took
almost [half a year](*https://github.com/spatie/data-transfer-object/issues/151) to get everything right. But in the end
we made the right choice. We took a strong, opinionated stance for static type systems, and reimagined the package from the ground
up, and thus made it future-proof.

---

I've got another example, from Laravel this time, the popular back-end framework. I work with it every day and absolutely love it. Though I must also admit there is room for improvement.
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

As well as represent some rules using objects:

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

And finally, use closures as well:

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

Should there be a moment though in software design where these old ties are broken? Where opinionated decisions can be made to improve such code bases, as well as the developer experience of people using them? I believe the majority of people would benefit: maintainers have less code and execution paths to worry about, while users have less micro-decisions to deal with, and thus decreasing the learning curve and cognitive load. 

I would like to see some more opinion-driven design, so that I don't have to be bothered with micro-decisions every other step. I believe it would result in better software, overall.

{{ cta:like }}

{{ cta:diary }}

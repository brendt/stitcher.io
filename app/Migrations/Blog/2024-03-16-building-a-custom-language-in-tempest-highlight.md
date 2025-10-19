Yesterday, I wrote about the _why_ of [making a new syntax highlighter](/blog/a-syntax-highlighter-that-doesnt-suck). Today I want to write about the _how_.

<iframe width="560" height="345" src="https://www.youtube.com/embed/cZugbAR8Fyg" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Let's explain how `tempest/highlight` works by implementing a new language — [Blade](https://laravel.com/docs/11.x/blade) is a good candidate. It looks something like this:

```blade
@if(! empty($items))
    <div class="container">
        Items: {{ count($items) }}.
    </div>
@endslot
```

In order to build such a new language, you need to understand _three_ concepts of how code is highlighted: _patterns_, _injections_, and _languages_.

### 1. Patterns

A _pattern_ represents part of code that should be highlighted. A _pattern_ can target a single keyword like `return` or `class`, or it could be any part of code, like for example a comment: `/* this is a comment */` or an attribute: `#[Get(uri: '/')]`.

Each _pattern_ is represented by a simple class that provides a regex pattern, and a `TokenType`. The regex pattern is used to match relevant content to this specific _pattern_, while the `TokenType` is an enum value that will determine how that specific _pattern_ is colored.

Here's an example of a simple _pattern_ to match the namespace of a PHP file:

```php
use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\Tokens\TokenType;

final readonly class NamespacePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return 'namespace (?<match>[\w\\\\]+)';
    }

    public function getTokenType(): TokenType
    {
        return TokenType::TYPE;
    }
}
```

Note that each pattern must include a regex capture group that's named `match`. The content that matched within this group will be highlighted.

For example, this regex `namespace (?<match>[\w\\\\]+)` says that every line starting with `namespace` should be taken into account, but only the part within the named group `(?<match>…)` will actually be colored. In practice that means that the namespace name matching `[\w\\\\]+`, will be colored.

Yes, you'll need some basic knowledge of regex. Head over to [https://regexr.com/](https://regexr.com/) if you need help, or take a look at the existing patterns in this repository.

**In summary:**

- Pattern classes provide a regex pattern that matches parts of code.
- Those regexes should contain a group named `match`, which is written like so `(?<match>…)`, this group represents the code that will actually be highlighted.
- Finally, a pattern provides a `{php}TokenType`, which is used to determine the highlight style for the specific match.

### 2. Injections

Once you've understood patterns, the next step is to understand _injections_. _Injections_ are used to highlight different languages within one code block. For example: HTML could contain CSS, which should be styled properly as well.

An _injection_ will tell the highlighter that it should treat a block of code as a different language. For example:

```html
<div>
    <x-slot name="styles">
        <style>
            body {
                background-color: red;
            }
        </style>
    </x-slot>
</div>
```

Everything within `{html}<style></style>` tags should be treated as CSS. That's done by injection classes:

```php
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Injection;
use Tempest\Highlight\IsInjection;

final readonly class CssInjection implements Injection
{
    use IsInjection;

    public function getPattern(): string
    {
        return '<style>(?<match>(.|\n)*)<\/style>';
    }

    public function parseContent(string $content, Highlighter $highlighter): string
    {
        return $highlighter->parse($content, 'css');
    }
}
```

Just like patterns, an _injection_ must provide a pattern. This pattern, for example, will match anything between style tags: `{html}<style>(?<match>(.|\n)*)<\/style>`.

The second step in providing an _injection_ is to parse the matched content into another language. That's what the `{php}parseContent()` method is for. In this case, we'll get all code between the style tags that was matched with the named `(?<match>…)` group, and parse that content as CSS instead of whatever language we're currently dealing with.

**In summary:**

- Injections provide a regex that matches a blob of code of language A, while in language B.
- Just like patterns, injection regexes should contain a group named `match`, which is written like so: `(?<match>…)`.
- Finally, an injection will use the highlighter to parse its matched content into another language.

### 3. Languages

The last concept to understand: _languages_ are classes that bring _patterns_ and _injections_ together. Take a look at the `{php}HtmlLanguage`, for example:

```php
class HtmlLanguage extends BaseLanguage
{
    public function getInjections(): array
    {
        return [
            ...parent::getInjections(),
            new PhpInjection(),
            new PhpShortEchoInjection(),
            new CssInjection(),
            new CssAttributeInjection(),
        ];
    }

    public function getPatterns(): array
    {
        return [
            ...parent::getPatterns(),
            new OpenTagPattern(),
            new CloseTagPattern(),
            new TagAttributePattern(),
            new HtmlCommentPattern(),
        ];
    }
}

```

This `{php}HtmlLanguage` class specifies the following things:

- PHP can be injected within HTML, both with the short echo tag `<?=` and longer `<?php` tags
- CSS can be injected as well, JavaScript support is still work in progress
- There are a bunch of patterns to highlight HTML tags properly

On top of that, it extends from `{php}BaseLanguage`. This is a language class that adds a bunch of cross-language injections, such as blurs and highlights. Your language doesn't _need_ to extend from `{php}BaseLanguage` and could implement `{php}Language` directly if you want to.

With these three concepts in place, let's bring everything together to explain how you can add your own languages.

### Adding custom languages

So we're adding [Blade](https://laravel.com/docs/11.x/blade) support. We could create a new language class and start from scratch, but it'd probably be easier to extend an existing language, `{php}HtmlLanguage` is probably the best. Let create a new `{php}BladeLanguage` class that extends from `{php}HtmlLanguage`:

```php
class BladeLanguage extends HtmlLanguage
{
    public function getInjections(): array
    {
        return [
            ...parent::getInjections(),
        ];
    }

    public function getPatterns(): array
    {
        return [
            ...parent::getPatterns(),
        ];
    }
}
```

With this class in place, we can start adding our own patterns and injections. Let's start with adding a pattern that matches all Blade keywords, which are always prepended with the `@` sign. Let's add it:

```php
final readonly class BladeKeywordPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\@[\w]+)\b';
    }

    public function getTokenType(): TokenType
    {
        return TokenType::KEYWORD;
    }
}
```

And register it in our `{php}BladeLanguage` class:

```php
    public function getPatterns(): array
    {
        return [
            ...parent::getPatterns(),
            new BladeKeywordPattern(),
        ];
    }
```

Next, there are a couple of places within Blade where you can write PHP code: within the `{blade}@php` keyword, as well as within keyword brackets: `{blade}@if (count(…))`. Let's write two injections for that:

```php
final readonly class BladePhpInjection implements Injection
{
    use IsInjection;

    public function getPattern(): string
    {
        return '\@php(?<match>(.|\n)*?)\@endphp';
    }

    public function parseContent(string $content, Highlighter $highlighter): string
    {
        return $highlighter->parse($content, 'php');
    }
}
```

```php
final readonly class BladeKeywordInjection implements Injection
{
    use IsInjection;

    public function getPattern(): string
    {
        return '(\@[\w]+)\s?\((?<match>.*)\)';
    }

    public function parseContent(string $content, Highlighter $highlighter): string
    {
        return $highlighter->parse($content, 'php');
    }
}
```

Let's add these to our `{php}BladeLanguage` class as well:

```php
    public function getInjections(): array
    {
        return [
            ...parent::getInjections(),
            new BladePhpInjection(),
            new BladeKeywordInjection(),
        ];
    }
```

Next, you can write `{{ … }}` and `{!! … !!}` to echo output. Whatever is between these brackets is also considered PHP, so, one more injection:

```php
final readonly class BladeEchoInjection implements Injection
{
    use IsInjection;

    public function getPattern(): string
    {
        return '({{|{!!)(?<match>.*)(}}|!!})';
    }

    public function parseContent(string $content, Highlighter $highlighter): string
    {
        return $highlighter->parse($content, 'php');
    }
}
```

And, finally, you can write Blade comments like so: `{{-- --}}`, this can be a simple pattern:

```php
final readonly class BladeCommentPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\{\{\-\-(.|\n)*?\-\-\}\})';
    }

    public function getTokenType(): TokenType
    {
        return TokenType::COMMENT;
    }
}
```

With all of that in place, the only thing left to do is to add our language to the highlighter:

```php
$highlighter->addLanguage('blade', new BladeLanguage());
```

And we're done! Blade support with just a handful of patterns and injections!

I think that the ability to extend from other languages and language injections are both really powerful to be able to quickly build new languages. Of course, **you're free to [send pull requests](https://github.com/tempestphp/highlight) with support for additional languages as well! Take a look at the [package's tests](https://github.com/tempestphp/highlight/tree/main/tests/Languages) to learn how to write tests for patterns and injections.**

{{ cta:mail }}
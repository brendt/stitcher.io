_Just a note up front: I wrote this post as a thought exercise, not as an absolute source of truth. I'd love to hear people disagree and tell me why, so don't hesitate to reply wherever you want._

---

You've probably used the strategy pattern before: [a behavioral pattern that enables selecting an algorithm at runtime](*https://en.wikipedia.org/wiki/Strategy_pattern).

Let's consider a classic example: the user provides some input either in the form of XML, JSON or an array; and we want that input to be parsed to a pretty JSON string.

So all these inputs:

```
'{"title":"test"}'
'<title>test</title>'
['title' => 'test']
```

Would convert to this:

```
{
    "title": "test"
}
```

Oh, there's one more requirement: we need these strategies to be extensible. Developers should be allowed to add their own strategies for dealing with other kinds of inputs: YAML, interfaces, iterable objects, whatever they need.

Let's take a look at the classic solution, and its problems.

---

Usually, we start by introducing some kind of interface that all strategies should implement:

```php
interface ParserInterface
{
    public function canParse(<hljs type>mixed</hljs> $input): bool;
    
    public function parse(<hljs type>mixed</hljs> $input): mixed;
}
```

Each strategy must define whether it can run on a given input, and provide its actual implementation.

Next we can provide several implementations of that interface:

```php
final class ArrayParser implements ParserInterface
{
    public function canParse(<hljs type>mixed</hljs> $input): bool
    {
        return <hljs prop>is_array</hljs>($input);
    }
    
    public function parse(<hljs type>mixed</hljs> $input): mixed
    {
        return <hljs prop>json_encode</hljs>($input, <hljs prop>JSON_PRETTY_PRINT</hljs>);
    }
}

final class JsonParser implements ParserInterface
{
    public function canParse(<hljs type>mixed</hljs> $input): bool
    {
        return 
            <hljs prop>is_string</hljs>($input) 
            && <hljs prop>str_starts_with</hljs>(<hljs prop>trim</hljs>($input), '{') 
            && <hljs prop>str_ends_with</hljs>(<hljs prop>trim</hljs>($input), '}');
    }
    
    public function parse(<hljs type>mixed</hljs> $input): mixed
    {
        return <hljs prop>json_encode</hljs>(
            <hljs prop>json_decode</hljs>($input), 
            <hljs prop>JSON_PRETTY_PRINT</hljs>
        );
    }
}

final class XmlParser implements ParserInterface
{
    public function canParse(<hljs type>mixed</hljs> $input): bool
    {
        return
            <hljs prop>is_string</hljs>($input) 
            && <hljs prop>str_starts_with</hljs>(<hljs prop>trim</hljs>($input), '<') 
            && <hljs prop>str_ends_with</hljs>(<hljs prop>trim</hljs>($input), '>');
    }
    
    public function parse(<hljs type>mixed</hljs> $input): mixed
    {
        return <hljs prop>json_encode</hljs>(
            <hljs prop>simplexml_load_string</hljs>(
                $input, 
                "SimpleXMLElement", 
                <hljs prop>LIBXML_NOCDATA</hljs>
            ), 
            <hljs prop>JSON_PRETTY_PRINT</hljs>
        );
    }
}
```

Full disclosure: these are very naive implementations. The strategy detection in the `<hljs prop>canParse</hljs>` method simply looks at the first and last character of the input string, and probably isn't fool-proof. Also: the XML decoding doesn't properly work; but it's good enough for the sake of this example.

The next step is to provide a class that developers can use as the public API, this one will use our different strategies underneath. It's configured by adding a set of strategy implementations, and exposes one `<hljs prop>parse</hljs>` method to the outside:

```php
final class Parser
{
    /** @var <hljs type>ParserInterface[]</hljs> */
    private <hljs type>array</hljs> <hljs prop>$parsers</hljs> = [];
    
    public function __construct() {
        $this
            -><hljs prop>addParser</hljs>(new <hljs type>ArrayParser</hljs>)
            -><hljs prop>addParser</hljs>(new <hljs type>JsonParser</hljs>)
            -><hljs prop>addParser</hljs>(new <hljs type>XmlParser</hljs>);
    }
    
    public function addParser(<hljs type>ParserInterface</hljs> $parser): self
    {
        $this-><hljs prop>parsers</hljs>[] = $parser;
        
        return $this;
    }
    
    public function parse(<hljs type>mixed</hljs> $input): mixed
    {
        foreach ($this-><hljs prop>parsers</hljs> as $parser) {
            if ($parser-><hljs prop>canParse</hljs>($input)) {
                return $parser-><hljs prop>parse</hljs>($input);
            }
        }
        
        throw new <hljs type>Exception</hljs>("Could not parse given input");
    }
}
```

And we're done, right? The user can now use our `<hljs type>Parser</hljs>` like so:

```php
$parser = new <hljs type>Parser</hljs>();

$parser-><hljs prop>parse</hljs>('{"title":"test"}');
$parser-><hljs prop>parse</hljs>('<title>test</title>');
$parser-><hljs prop>parse</hljs>(['title' => 'test']);
```

And the output will always be a pretty JSON string.

Well… let's take a look at it from the other side: a developer who wants to extend the existing parser with their own functionality: an implementation that transforms a `<hljs type>Request</hljs>` class to a JSON string. We designed our parser with the strategy pattern for this exact reason; so, easy enough:

```php
final class RequestParser implements ParserInterface
{
    public function canParse(<hljs type>mixed</hljs> $input): bool
    {
        return $input instanceof <hljs type>Request</hljs>;
    }
    
    public function parse(<hljs type>mixed</hljs> $input): mixed
    {
        return <hljs prop>json_encode</hljs>([
            'method' => $input-><hljs prop>method</hljs>,
            'headers' => $input-><hljs prop>headers</hljs>,
            'body' => $input-><hljs prop>body</hljs>,
        ], <hljs prop>JSON_PRETTY_PRINT</hljs>);
    }
}
```

And let's assume our parser is registered somewhere in an IoC container, we can add it like so:

```php
<hljs type>Container</hljs>::<hljs prop>singleton</hljs>(
    <hljs type>Parser</hljs>::class,
    <hljs keyword>fn</hljs> () => (new <hljs type>Parser</hljs>)-><hljs prop>addParser</hljs>(new <hljs type>RequestParser</hljs>);
);
```

And we're done!

Except… have you spotted the one issue? If you've used the strategy pattern in this way before (many open source packages apply it), you might already have an idea.

It's in our `<hljs type>RequestParser</hljs>::<hljs prop>parse</hljs>` method:

```php
public function parse(<hljs type>mixed</hljs> $input): mixed
{
    return <hljs prop>json_encode</hljs>([
        'method' => $input-><hljs prop>method</hljs>,
        'headers' => $input-><hljs prop>headers</hljs>,
        'body' => $input-><hljs prop>body</hljs>,
    ], <hljs prop>JSON_PRETTY_PRINT</hljs>);
}
```

The problem here is that we have no clue about the actual type of `$input`. We know it should be a `<hljs type>Request</hljs>` object because of the check in `<hljs prop>canParse</hljs>`, but our IDE of course doesn't know that. So we'll have to help it a little bit, either by providing a docblock:

```php
/**
 * @var <hljs type>mixed|Request</hljs> $input 
 */
public function parse(<hljs type>mixed</hljs> $input): mixed
{
    return <hljs prop>json_encode</hljs>([
        'method' => $input-><hljs prop>method</hljs>,
        'headers' => $input-><hljs prop>headers</hljs>,
        'body' => $input-><hljs prop>body</hljs>,
    ], <hljs prop>JSON_PRETTY_PRINT</hljs>);
}
```

Or by doing the `<hljs keyword>instanceof</hljs>` check again:

```php
public function parse(<hljs type>mixed</hljs> $input): mixed
{
    if (! $input instanceof <hljs type>Request</hljs>) {
        // error?
    }
    
    return <hljs prop>json_encode</hljs>([
        'method' => $input-><hljs prop>method</hljs>,
        'headers' => $input-><hljs prop>headers</hljs>,
        'body' => $input-><hljs prop>body</hljs>,
    ], <hljs prop>JSON_PRETTY_PRINT</hljs>);
}
```

So because of how we designed our `<hljs type>ParserInterface</hljs>`, developers who want to implement it, will have to do double work:

```php
final class RequestParser implements ParserInterface
{
    public function canParse(<hljs type>mixed</hljs> $input): bool
    {
        <hljs green>return $input instanceof <hljs type>Request</hljs>;</hljs>
    }
    
    public function parse(<hljs type>mixed</hljs> $input): mixed
    {
        <hljs green>if (! $input instanceof <hljs type>Request</hljs>) {</hljs>
            // error?
        }
        
        // …
    }
}
```

This kind of code duplication isn't the end of the world, at most it's a minor inconvenience. Most developers won't even bat an eye.

But I do. As a package maintainer, I want my public APIs to be as intuitive and frictionless as possible. To me, that means that static insights are a crucial part of the developer experience, and I don't want the users of my code to be hindered because of how I designed this parser.

So, let's discuss a couple of ways to fix this problem.

{{ cta:dynamic }}

## No more duplication

If the problem of duplication happens because we've split our `<hljs prop>canParse</hljs>` and `<hljs prop>parse</hljs>` methods, maybe the easiest solution is to simply… not split them?

What if we design our strategy classes in such a way that they will throw an exception if they can't parse it, instead of using an explicit conditional?

```php
interface ParserInterface
{
    /**
     * @throws <hljs type>CannotParse</hljs> 
     *         When this parser can't parse 
     *         the given input. 
     */
    public function parse(<hljs type>mixed</hljs> $input): mixed;
}

final class RequestParser implements ParserInterface
{
    public function parse(<hljs type>mixed</hljs> $input): mixed
    {
        if (! $input instanceof <hljs type>Request</hljs>) {
            throw new <hljs type>CannotParse</hljs>;
        }
        
        // …
    }
}
```

Our generic parser class would change like so:


```php
final class Parser
{
    // …
    
    public function parse(<hljs type>mixed</hljs> $input): mixed
    {
        foreach ($this-><hljs prop>parsers</hljs> as $parser) {
            try {
                return $parser-><hljs prop>parse</hljs>($input);
            } catch (<hljs type>ParseException</hljs>) {
                continue;
            }
        }
        
        throw new <hljs type>Exception</hljs>("Could not parse given input");
    }
}
```

Of course, now we're opening up the rabbit hole of "what an exception is" and whether we're allowed to use exceptions to control our program flow in this way. My personal opinion is "yes, definitely"; because passing a string to a method that can only work with a `<hljs type>Request</hljs>` object is in fact, an _exception_ to the rule. At least, that's my definition.

Some people might opt for returning `<hljs keyword>null</hljs>` instead of throwing an exception, although that feels more wrong to me: `<hljs keyword>null</hljs>` doesn't communicate that this particular method wasn't able to handle the input. In fact, `<hljs keyword>null</hljs>` could very well be a valid result from this parser, depending on its requirements. So no, no `<hljs keyword>null</hljs>` for me.

However, I share the opinion that probably a couple of people have when reading this: either returning `<hljs keyword>null</hljs>` or throwing an exception doesn't feel like the cleanest solution. If we're embarking on this journey for the sole purpose of fixing a detail that only a handful of developers might be bothered about, we might explore other options as well, and dive even deeper into the rabbit hole. 

## Types

We've written this manual check to guard against invalid input: `$input <hljs keyword>instanceof</hljs> <hljs type>Request</hljs>`; but did you know there's an automated way for PHP to do these kinds of checks? Its built-in type system! Why bother rewriting stuff manually that PHP can do for us behind the scenes? Why not simply type hint on `<hljs type>Request</hljs>`?

```php
final class RequestParser implements ParserInterface
{
    public function parse(<hljs type>Request</hljs> $input): mixed
    {
        // …
    }
}
```

Well we can't, because of two problems:

- We're not allowed to narrow parameter types from `<hljs type>mixed</hljs>` to `<hljs type>Request</hljs>` according to the [Liskov Substitution Principle](/blog/liskov-and-type-safety), which is enforced by PHP and our `<hljs type>ParserInterface</hljs>`; and
- not every input can be represented as a dedicated type: both XML and JSON are strings, there's some ambiguity there.

So, end of story? Well… we're already so deep into the rabbit hole, we might as well give it a shot. 

Let's start by imagining that the two problems mentioned aren't an issue: could we in fact design our parser in such a way that it's able to detect each strategy's accepted input, and select the proper strategy based on that information?

We sure can! The most simple solution is to loop over all strategies, try to pass them some input and continue if they can't handle it; let PHP's type system handle the rest:

```php
final class Parser
{
    public function handle(<hljs type>mixed</hljs> $input): mixed
    {
        foreach ($this-><hljs prop>parsers</hljs> as $parser) {
            try {
                return $parser-><hljs prop>parse</hljs>($input);
            } catch (<hljs type>TypeError</hljs>) {
                continue;
            }
        }
        
        throw new <hljs type>Exception</hljs>("Could not parse given input");
    }
}
```

I actually prefer this approach over any kind of runtime reflection trying to determine which method can accept which input. **Let's not try to recreate PHP's type checker at runtime**. The only real requirement for this approach to work is that your strategy methods won't have any side effects and that they'll always properly type hint their input. That's one of my personal cornerstones when programming, and so I have no problems writing code that assumes this principle.

Ok so it is possible to match any given input to its correct strategy based on its method signature. But we still need to deal with our two initial problems.

The first one is that we're not allowed to write this:

```php
final class RequestParser implements ParserInterface
{
    public function parse(<hljs type striped>Request</hljs> $input): mixed
    {
        // …
    }
}
```

Because we defined the signature of `<hljs prop>parse</hljs>` in our `<hljs type>ParserInterface</hljs>` like so:

```php
interface ParserInterface
{
    public function parse(<hljs type>mixed</hljs> $input): mixed;
}
```

We can't narrow down parameter types, we can only widen them; that's called [contravariance](/blog/liskov-and-type-safety).

So on the one hand we have an interface that says that our strategies can take _any_ type of input (`<hljs type>mixed</hljs>`); but on the other hand we have our strategy classes that tell us they can only work with a _specific_ type of input.

_If_ we want to go further into the rabbit hole, then there's no other conclusion to make than that our interface isn't actually telling the truth: we're not making strategies that work with _any_ kind of input, and so it doesn't make sense to have an interface tell us that we do. This interface is essentially telling a lie, and there's no reason to keep it.

Well, actually: there _is_ a reason to have this interface: it guides a developer in understanding how they can add their own strategies, without having to rely on documentation. When a developer sees this method signature:

```php
final class Parser
{
    // …
    
    public function addParser(<hljs type>ParserInterface</hljs> $parser): self
    {
        $this-><hljs prop>parsers</hljs>[] = $parser;
        
        return $this;
    }
}
```

It's clear to them that they'll need to implement `<hljs type>ParserInterface</hljs>` for their custom strategies to work. So I'd say that getting rid of this interface might do more harm than good, because without it, developers are operating in the dark.

There is _one_ solution that I can think of that can counter this problem: accepting callables.

```php
public function addParser(<hljs type>callable</hljs> $parser): self
{
    $this-><hljs prop>parsers</hljs>[] = $parser;
    
    return $this;
}
```

`<hljs type>callable</hljs>` is a _special_ type in PHP, because it doesn't only cover functions and closures, but also invokable objects. The only real thing missing here is that we can't tell — with certainty — from our code what our callables should look like.

We've established a rule saying that it should accept any kind of input that it can work with, but there's no way we can tell developers extending our code that, without providing an additional docblock. This is definitely a downside of this approach, and might be reason enough for you not to go with it.

I personally don't mind, I think the code duplication we had in the beginning and manual type validation annoys me more than having to read a docblock:

```php
/**
 * @param <hljs type>callable</hljs> $parser A callable accepting one typed parameter.
 *                         This parameter's type is used to match 
 *                         the input given to the parser to the
 *                         correct parser implementation.
 */
public function addParser(<hljs type>callable</hljs> $parser): self
{
    $this-><hljs prop>parsers</hljs>[] = $parser;
    
    return $this;
}
```

Then there's our second problem: not everything can be represented by a type. For example: both JSON and XML parsers should match on a `<hljs type>string</hljs>` of either JSON or XML, and we can't type hint those. I can think of two solutions.

- Do some manual checks in the `<hljs prop>parse</hljs>` method for these edge cases, and throw an `TypeError` when they don't match; or
- introduce `<hljs type>JsonString</hljs>` and `<hljs type>XmlString</hljs>` as custom classes, and have a factory first convert those raw strings to their proper types.

The first option would look like this:

```php
final class JsonParser
{
    public function __invoke(<hljs type>string</hljs> $input): string
    {
        if (
            ! <hljs prop>str_starts_with</hljs>(<hljs prop>trim</hljs>($input), '{') 
            || ! <hljs prop>str_ends_with</hljs>(<hljs prop>trim</hljs>($input), '}')
        ) {
            throw new <hljs type>TypeError</hljs>("Not a valid JSON string");   
        }
        
        return <hljs prop>json_encode</hljs>(
            <hljs prop>json_decode</hljs>($input), 
            <hljs prop>JSON_PRETTY_PRINT</hljs>
        );
    }
}

final class XmlParser 
{
    public function __invoke(<hljs type>string</hljs> $input): string
    {
        if (
            ! <hljs prop>str_starts_with</hljs>(<hljs prop>trim</hljs>($input), '<') 
            || ! <hljs prop>str_ends_with</hljs>(<hljs prop>trim</hljs>($input), '>')
        ) {
            throw new <hljs type>TypeError</hljs>("Not a valid XML string");
        }
        
        return <hljs prop>json_encode</hljs>(
            <hljs prop>simplexml_load_string</hljs>(
                $input, 
                "SimpleXMLElement", 
                <hljs prop>LIBXML_NOCDATA</hljs>
            ), 
            <hljs prop>JSON_PRETTY_PRINT</hljs>
        );
    }
}
```

The second one, having a custom class for `<hljs type>JsonString</hljs>` and `<hljs type>XmlString</hljs>`, would look something like this:


```php
final class JsonParser
{
    public function __invoke(<hljs type>JsonString</hljs> $input): string
    {
        return <hljs prop>json_encode</hljs>(
            <hljs prop>json_decode</hljs>($input), 
            <hljs prop>JSON_PRETTY_PRINT</hljs>
        );
    }
}

final class XmlParser 
{
    public function __invoke(<hljs type>XmlString</hljs> $input): string
    {
        return <hljs prop>json_encode</hljs>(
            <hljs prop>simplexml_load_string</hljs>(
                $input, 
                "SimpleXMLElement", 
                <hljs prop>LIBXML_NOCDATA</hljs>
            ), 
            <hljs prop>JSON_PRETTY_PRINT</hljs>
        );
    }
}
```

But don't forget that we'd also need to introduce a factory to convert a string to its proper type, which means quite a lot of overhead.

On a final note, `<hljs type>callable</hljs>` has another advantage: users aren't bound to using invokable classes. Depending on their needs and how they test, they could get away with simply adding closures:

```php
<hljs type>Container</hljs>::<hljs prop>singleton</hljs>(
    <hljs type>Parser</hljs>::class,
    <hljs keyword>fn</hljs> () => (new <hljs type>Parser</hljs>)-><hljs prop>addParser</hljs>(
        <hljs keyword>fn</hljs> (<hljs type>Request</hljs> $request) => <hljs prop>json_encode</hljs>([
            'method' => $request-><hljs prop>method</hljs>,
            'headers' => $request-><hljs prop>headers</hljs>,
            'body' => $request-><hljs prop>body</hljs>,
        ], <hljs prop>JSON_PRETTY_PRINT</hljs>)
    );
);
```

---

Are there downsides to this approach? Definitely. Just like there are downsides to the original solution where we had lots of code duplication. I personally think that, from a developer experience point of view; it's worth considering alternatives to the original way of how we implement dynamic strategies; and I can imagine some projects benefiting from it.

What do you think? Let me know via [Twitter](*https://twitter.com/brendt_gd) or [email](mailto:brendt@stitcher.io); don't hesitate to say I'm slowly going crazy if you think so!

{{ cta:like }}

{{ cta:mail }}

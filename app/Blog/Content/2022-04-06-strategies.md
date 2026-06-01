---
title: 'Dynamic Strategies'
---

_Just a note up front: I wrote this post as a thought exercise, not as an absolute source of truth. I'd love to hear people disagree and tell me why, so don't hesitate to reply wherever you want._

{{ ad:carbon }}

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
    public function canParse(mixed $input): bool;
    
    public function parse(mixed $input): mixed;
}
```

Each strategy must define whether it can run on a given input, and provide its actual implementation.

Next we can provide several implementations of that interface:

```php
final class ArrayParser implements ParserInterface
{
    public function canParse(mixed $input): bool
    {
        return is_array($input);
    }
    
    public function parse(mixed $input): mixed
    {
        return json_encode($input, JSON_PRETTY_PRINT);
    }
}

final class JsonParser implements ParserInterface
{
    public function canParse(mixed $input): bool
    {
        return 
            is_string($input) 
            && str_starts_with(trim($input), '{') 
            && str_ends_with(trim($input), '}');
    }
    
    public function parse(mixed $input): mixed
    {
        return json_encode(
            json_decode($input), 
            JSON_PRETTY_PRINT
        );
    }
}

final class XmlParser implements ParserInterface
{
    public function canParse(mixed $input): bool
    {
        return
            is_string($input) 
            && str_starts_with(trim($input), '<') 
            && str_ends_with(trim($input), '>');
    }
    
    public function parse(mixed $input): mixed
    {
        return json_encode(
            simplexml_load_string(
                $input, 
                "SimpleXMLElement", 
                LIBXML_NOCDATA
            ), 
            JSON_PRETTY_PRINT
        );
    }
}
```

Full disclosure: these are very naive implementations. The strategy detection in the `canParse` method simply looks at the first and last character of the input string, and probably isn't fool-proof. Also: the XML decoding doesn't properly work; but it's good enough for the sake of this example.

The next step is to provide a class that developers can use as the public API, this one will use our different strategies underneath. It's configured by adding a set of strategy implementations, and exposes one `parse` method to the outside:

```php
final class Parser
{
    /** @var ParserInterface[] */
    private array $parsers = [];
    
    public function __construct() {
        $this
            ->addParser(new ArrayParser)
            ->addParser(new JsonParser)
            ->addParser(new XmlParser);
    }
    
    public function addParser(ParserInterface $parser): self
    {
        $this->parsers[] = $parser;
        
        return $this;
    }
    
    public function parse(mixed $input): mixed
    {
        foreach ($this->parsers as $parser) {
            if ($parser->canParse($input)) {
                return $parser->parse($input);
            }
        }
        
        throw new Exception("Could not parse given input");
    }
}
```

And we're done, right? The user can now use our `Parser` like so:

```php
$parser = new Parser();

$parser->parse('{"title":"test"}');
$parser->parse('<title>test</title>');
$parser->parse(['title' => 'test']);
```

And the output will always be a pretty JSON string.

Well… let's take a look at it from the other side: a developer who wants to extend the existing parser with their own functionality: an implementation that transforms a `Request` class to a JSON string. We designed our parser with the strategy pattern for this exact reason; so, easy enough:

```php
final class RequestParser implements ParserInterface
{
    public function canParse(mixed $input): bool
    {
        return $input instanceof Request;
    }
    
    public function parse(mixed $input): mixed
    {
        return json_encode([
            'method' => $input->method,
            'headers' => $input->headers,
            'body' => $input->body,
        ], JSON_PRETTY_PRINT);
    }
}
```

And let's assume our parser is registered somewhere in an IoC container, we can add it like so:

```php
Container::singleton(
    Parser::class,
    fn () => (new Parser)->addParser(new RequestParser);
);
```

And we're done!

Except… have you spotted the one issue? If you've used the strategy pattern in this way before (many open source packages apply it), you might already have an idea.

It's in our `RequestParser::parse` method:

```php
public function parse(mixed $input): mixed
{
    return json_encode([
        'method' => $input->method,
        'headers' => $input->headers,
        'body' => $input->body,
    ], JSON_PRETTY_PRINT);
}
```

The problem here is that we have no clue about the actual type of `$input`. We know it should be a `Request` object because of the check in `canParse`, but our IDE of course doesn't know that. So we'll have to help it a little bit, either by providing a docblock:

```php
/**
 * @var mixed|Request $input 
 */
public function parse(mixed $input): mixed
{
    return json_encode([
        'method' => $input->method,
        'headers' => $input->headers,
        'body' => $input->body,
    ], JSON_PRETTY_PRINT);
}
```

Or by doing the `instanceof` check again:

```php
public function parse(mixed $input): mixed
{
    if (! $input instanceof Request) {
        // error?
    }
    
    return json_encode([
        'method' => $input->method,
        'headers' => $input->headers,
        'body' => $input->body,
    ], JSON_PRETTY_PRINT);
}
```

So because of how we designed our `ParserInterface`, developers who want to implement it, will have to do double work:

```php
final class RequestParser implements ParserInterface
{
    public function canParse(mixed $input): bool
    {
        return $input instanceof Request;
    }
    
    public function parse(mixed $input): mixed
    {
        if (! $input instanceof Request) {
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

If the problem of duplication happens because we've split our `canParse` and `parse` methods, maybe the easiest solution is to simply… not split them?

What if we design our strategy classes in such a way that they will throw an exception if they can't parse it, instead of using an explicit conditional?

```php
interface ParserInterface
{
    /**
     * @throws CannotParse 
     *         When this parser can't parse 
     *         the given input. 
     */
    public function parse(mixed $input): mixed;
}

final class RequestParser implements ParserInterface
{
    public function parse(mixed $input): mixed
    {
        if (! $input instanceof Request) {
            throw new CannotParse;
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
    
    public function parse(mixed $input): mixed
    {
        foreach ($this->parsers as $parser) {
            try {
                return $parser->parse($input);
            } catch (ParseException) {
                continue;
            }
        }
        
        throw new Exception("Could not parse given input");
    }
}
```

Of course, now we're opening up the rabbit hole of "what an exception is" and whether we're allowed to use exceptions to control our program flow in this way. My personal opinion is "yes, definitely"; because passing a string to a method that can only work with a `Request` object is in fact, an _exception_ to the rule. At least, that's my definition.

Some people might opt for returning `null` instead of throwing an exception, although that feels more wrong to me: `null` doesn't communicate that this particular method wasn't able to handle the input. In fact, `null` could very well be a valid result from this parser, depending on its requirements. So no, no `null` for me.

However, I share the opinion that probably a couple of people have when reading this: either returning `null` or throwing an exception doesn't feel like the cleanest solution. If we're embarking on this journey for the sole purpose of fixing a detail that only a handful of developers might be bothered about, we might explore other options as well, and dive even deeper into the rabbit hole. 

## Types

We've written this manual check to guard against invalid input: `$input instanceof Request`; but did you know there's an automated way for PHP to do these kinds of checks? Its built-in type system! Why bother rewriting stuff manually that PHP can do for us behind the scenes? Why not simply type hint on `Request`?

```php
final class RequestParser implements ParserInterface
{
    public function parse(Request $input): mixed
    {
        // …
    }
}
```

Well we can't, because of two problems:

- We're not allowed to narrow parameter types from `mixed` to `Request` according to the [Liskov Substitution Principle](/blog/liskov-and-type-safety), which is enforced by PHP and our `ParserInterface`; and
- not every input can be represented as a dedicated type: both XML and JSON are strings, there's some ambiguity there.

So, end of story? Well… we're already so deep into the rabbit hole, we might as well give it a shot. 

Let's start by imagining that the two problems mentioned aren't an issue: could we in fact design our parser in such a way that it's able to detect each strategy's accepted input, and select the proper strategy based on that information?

We sure can! The most simple solution is to loop over all strategies, try to pass them some input and continue if they can't handle it; let PHP's type system handle the rest:

```php
final class Parser
{
    public function handle(mixed $input): mixed
    {
        foreach ($this->parsers as $parser) {
            try {
                return $parser->parse($input);
            } catch (TypeError) {
                continue;
            }
        }
        
        throw new Exception("Could not parse given input");
    }
}
```

I actually prefer this approach over any kind of runtime reflection trying to determine which method can accept which input. **Let's not try to recreate PHP's type checker at runtime**. The only real requirement for this approach to work is that your strategy methods won't have any side effects and that they'll always properly type hint their input. That's one of my personal cornerstones when programming, and so I have no problems writing code that assumes this principle.

Ok so it is possible to match any given input to its correct strategy based on its method signature. But we still need to deal with our two initial problems.

The first one is that we're not allowed to write this:

```php
final class RequestParser implements ParserInterface
{
    public function parse(Request $input): mixed
    {
        // …
    }
}
```

Because we defined the signature of `parse` in our `ParserInterface` like so:

```php
interface ParserInterface
{
    public function parse(mixed $input): mixed;
}
```

We can't narrow down parameter types, we can only widen them; that's called [contravariance](/blog/liskov-and-type-safety).

So on the one hand we have an interface that says that our strategies can take _any_ type of input (`mixed`); but on the other hand we have our strategy classes that tell us they can only work with a _specific_ type of input.

_If_ we want to go further into the rabbit hole, then there's no other conclusion to make than that our interface isn't actually telling the truth: we're not making strategies that work with _any_ kind of input, and so it doesn't make sense to have an interface tell us that we do. This interface is essentially telling a lie, and there's no reason to keep it.

Well, actually: there _is_ a reason to have this interface: it guides a developer in understanding how they can add their own strategies, without having to rely on documentation. When a developer sees this method signature:

```php
final class Parser
{
    // …
    
    public function addParser(ParserInterface $parser): self
    {
        $this->parsers[] = $parser;
        
        return $this;
    }
}
```

It's clear to them that they'll need to implement `ParserInterface` for their custom strategies to work. So I'd say that getting rid of this interface might do more harm than good, because without it, developers are operating in the dark.

There is _one_ solution that I can think of that can counter this problem: accepting callables.

```php
public function addParser(callable $parser): self
{
    $this->parsers[] = $parser;
    
    return $this;
}
```

`callable` is a _special_ type in PHP, because it doesn't only cover functions and closures, but also invokable objects. The only real thing missing here is that we can't tell — with certainty — from our code what our callables should look like.

We've established a rule saying that it should accept any kind of input that it can work with, but there's no way we can tell developers extending our code that, without providing an additional docblock. This is definitely a downside of this approach, and might be reason enough for you not to go with it.

I personally don't mind, I think the code duplication we had in the beginning and manual type validation annoys me more than having to read a docblock:

```php
/**
 * @param callable $parser A callable accepting one typed parameter.
 *                         This parameter's type is used to match 
 *                         the input given to the parser to the
 *                         correct parser implementation.
 */
public function addParser(callable $parser): self
{
    $this->parsers[] = $parser;
    
    return $this;
}
```

Then there's our second problem: not everything can be represented by a type. For example: both JSON and XML parsers should match on a `string` of either JSON or XML, and we can't type hint those. I can think of two solutions.

- Do some manual checks in the `parse` method for these edge cases, and throw an `TypeError` when they don't match; or
- introduce `JsonString` and `XmlString` as custom classes, and have a factory first convert those raw strings to their proper types.

The first option would look like this:

```php
final class JsonParser
{
    public function __invoke(string $input): string
    {
        if (
            ! str_starts_with(trim($input), '{') 
            || ! str_ends_with(trim($input), '}')
        ) {
            throw new TypeError("Not a valid JSON string");   
        }
        
        return json_encode(
            json_decode($input), 
            JSON_PRETTY_PRINT
        );
    }
}

final class XmlParser 
{
    public function __invoke(string $input): string
    {
        if (
            ! str_starts_with(trim($input), '<') 
            || ! str_ends_with(trim($input), '>')
        ) {
            throw new TypeError("Not a valid XML string");
        }
        
        return json_encode(
            simplexml_load_string(
                $input, 
                "SimpleXMLElement", 
                LIBXML_NOCDATA
            ), 
            JSON_PRETTY_PRINT
        );
    }
}
```

The second one, having a custom class for `JsonString` and `XmlString`, would look something like this:


```php
final class JsonParser
{
    public function __invoke(JsonString $input): string
    {
        return json_encode(
            json_decode($input), 
            JSON_PRETTY_PRINT
        );
    }
}

final class XmlParser 
{
    public function __invoke(XmlString $input): string
    {
        return json_encode(
            simplexml_load_string(
                $input, 
                "SimpleXMLElement", 
                LIBXML_NOCDATA
            ), 
            JSON_PRETTY_PRINT
        );
    }
}
```

But don't forget that we'd also need to introduce a factory to convert a string to its proper type, which means quite a lot of overhead.

On a final note, `callable` has another advantage: users aren't bound to using invokable classes. Depending on their needs and how they test, they could get away with simply adding closures:

```php
Container::singleton(
    Parser::class,
    fn () => (new Parser)->addParser(
        fn (Request $request) => json_encode([
            'method' => $request->method,
            'headers' => $request->headers,
            'body' => $request->body,
        ], JSON_PRETTY_PRINT)
    );
);
```

---

Are there downsides to this approach? Definitely. Just like there are downsides to the original solution where we had lots of code duplication. I personally think that, from a developer experience point of view; it's worth considering alternatives to the original way of how we implement dynamic strategies; and I can imagine some projects benefiting from it.

What do you think? Let me know via [Twitter](*https://twitter.com/brendt_gd) or [email](mailto:brendt@stitcher.io); don't hesitate to say I'm slowly going crazy if you think so!

{{ cta:like }}

{{ cta:mail }}

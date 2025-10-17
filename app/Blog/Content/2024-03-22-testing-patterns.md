---
title: 'Testing Patterns'
disableAds: true
---

While building [tempest/highlight](/blog/a-syntax-highlighter-that-doesnt-suck), I came across an interesting design problem. One of its core components is a concept called "patterns"; these are classes that match a very specific part of code-to-be-highlighted using regex. Part of my test suite's responsibility is to test each of these patterns individually, to make sure they match the correct tokens, and don't match any incorrect ones.

Right now, tempest/highlight counts 109 pattern classes, a handful of them representing a collection of patterns such as keywords or operators. Take, for example, the `{php}NewObjectPattern` that matches PHP class names when they are used to create a new object:

```php
final readonly class NewObjectPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return 'new (?<match>[\w]+)';
    }

    public function getTokenType(): TokenType
    {
        return TokenType::TYPE;
    }
}

```

With 109 patterns (and that number is still growing), the question arises: how to test them? I could write individual tests for all of them, which is what I started out with. That grew into a mess pretty quickly though, so I created a trait called `{php}TestsPatterns` which has this method:

```php
public function assertMatches(
    Pattern $pattern,
    string $content,
    string|array|null $expected,
): void {
    $matches = $pattern->match($content);

    if (is_string($expected)) {
        $expected = [$expected];
    }

    if ($expected === null) {
        $this->assertCount(
            expectedCount: 0,
            haystack: $matches['match'],
            message: sprintf(
                "Expected there to be no matches at all in %s, but there were: %s",
                $pattern::class,
                var_export($matches['match'], true),
            )
        );

        return;
    }

    foreach ($expected as $key => $expectedValue) {
        $this->assertSame(
            expected: $expectedValue,
            actual: $matches['match'][$key][0],
            message: sprintf(
                "Pattern in %s did not match %s, found %s instead.",
                $pattern::class,
                var_export($expectedValue, true),
                var_export($matches['match'][$key][0], true),
            ),
        );
    }
}
```

This trait can be used like so:

```php

class NewObjectPatternTest extends TestCase
{
    use TestsPatterns;

    public function test_pattern()
    {
        $this->assertMatches(
            pattern: new NewObjectPattern(),
            content: 'new Foo()',
            expected: 'Foo',
        );

        $this->assertMatches(
            pattern: new NewObjectPattern(),
            content: '(new Foo)',
            expected: 'Foo',
        );
    }
}
```

This trait significantly reduced the amount of code I had to write: it takes a `{php}Pattern` object, and checks whether a given input returns a matching output.

Good enough? Well, not so fast. Writing tests this way for 109 patterns actually gets pretty boring, pretty fast. I believe that to write a thorough test suite, writing tests need to be as frictionless as possible, otherwise people (me) will just skip writing tests in the long run.

So, I wondered: "can I reduce this friction even more?"

[Data providers](https://blog.martinhujer.cz/how-to-use-data-providers-in-phpunit/) came to mind: what if I made one class that contained all my test cases, and only needed to provide the data?

It would look something like this:

```php

final class PatternsTest extends TestCase
{
    use TestsPatterns;

    #[Test]
    #[DataProvider('patterns')]
    public function test_patterns_with_attribute(Pattern $pattern, string $input, string $output)
    {
        $this->assertMatches(
            pattern: $pattern,
            content: $input,
            expected: $output,
        );
    }

    public static function patterns(): array
    {
        return [
            [new NewObjectPattern(), 'new Foo()', 'Foo'],     
            [new NewObjectPattern(), '(new Foo)', 'Foo'],     
            [new NewObjectPattern(), 'new Foo', 'Foo'],     
            
            // …
        ];   
    }
}
```

Now there is even less duplicated setup code to write! However, there is a glaring problem: this approach scales poorly. Can you imagine having all pattern tests in one test file? Luckily, PhpStorm allows to run a single data provider entry, and is able to tell you which specific data provider entry failed, so there is some fine-grained control:

![](/img/blog/pattern-test/phpstorm.png)

But still… hundreds, potentially thousands, of tests in the same file doesn't seem like an approach that would work in the long run.

Data providers gave me another idea though. What if there's one "main test" that's responsible for testing all patterns, but what if its data provider entries were aggregated from separate files? 

What if… 

What if I kept patterns and their specific tests together? Would that be possible? 

Definitely! How about using attributes on the pattern class itself?

```php
#[PatternTest(input: 'new Foo()', output: 'Foo')]
#[PatternTest(input: '(new Foo)', output: 'Foo')]
#[PatternTest(input: 'new Foo', output: 'Foo')]
final readonly class NewObjectPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return 'new (?<match>[\w]+)';
    }

    public function getTokenType(): TokenType
    {
        return TokenType::TYPE;
    }
}
```

With these attributes in place, I could now rewrite my data provider method like so:

```php
public static function patterns(): Generator
{
    $patternFiles = glob(__DIR__ . '/../src/Languages/*/Patterns/**.php');

    foreach ($patternFiles as $patternFile) {
        $className = str_replace(
            search: [__DIR__ . '/../src/', '/', '.php'],
            replace: ['Tempest\\Highlight\\', '\\', ''],
            subject: $patternFile,
        );

        $reflectionClass = new ReflectionClass($className);

        $attributes = $reflectionClass->getAttributes(PatternTest::class);

        foreach ($attributes as $attribute) {
            /** @var PatternTest $patternTest */
            $patternTest = $attribute->newInstance();

            yield [$reflectionClass->newInstance(), $patternTest];
        }
    }
}
```

Let me quickly run you through what happens. First, the data provider scans all classes in the right directories:

```php
$patternFiles = glob(__DIR__ . '/../src/Languages/*/Patterns/**.php');
```

Next, it gathers one of more `{php}PatternTest` attributes from these classes:

```php
$reflectionClass = new ReflectionClass($className);

$attributes = $reflectionClass->getAttributes(PatternTest::class);
```

Finally, each of those attributes is used to generate a test:

```php
foreach ($attributes as $attribute) {
    /** @var PatternTest $patternTest */
    $patternTest = $attribute->newInstance();

    yield [$reflectionClass->newInstance(), $patternTest];
}
```

And… that's it! In this case, I find this approach to be very handy: whenever I create a new pattern class, the first thing I do is add a couple of pattern tests to it so that I have an example to look at. There's also no need to worry about performance overhead: the whole testsuite for `tempest/highlight` runs in 50ms.

The only downside to this approach is that you cannot run a _specific_ pattern test on its own, without having first run the whole testsuite. PhpStorm is able to run data provider entries individually when they are listed within the data provider method, but filling that array dynamically of course prevents PhpStorm from detecting that. 

You can rerun specific pattern tests when they failed, and I find that adding a good error message helps you to quickly find the problem:

![](/img/blog/pattern-test/phpstorm-2.png)

I'll acknowledge that this is indeed a minor downside to this approach. However, I find that for this specific use case, I'm saving lots of time, and I've removed the majority of friction while testing `tempest/highlight`. In the end, for me, it's a win.

{{ cta:mail }}
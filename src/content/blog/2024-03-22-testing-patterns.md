While building [tempest/highlight](/blog/a-syntax-highlighter-that-doesnt-suck), I came across an interesting design problem. One of the core components of my highlighter package are so-called "Patterns": classes that match a very specific part of code-to-be-highlighted using regexes. Part of my test suite is to test each of these patterns individually, to make sure they match the correct tokens, and don't match the incorrect ones.

Right now, tempest/highlight counts 109 pattern classes, a handful of them representing a collection of patterns such as keywords or operators. The rest represent a specific pattern to match. Take, for example, the `{php}NewObjectPattern` that matches PHP class names when they are used to create new object with:

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

In case you're curious, I wrote about this pattern in depth in [this previous post](/blog/building-a-custom-language-in-tempest-highlight).

With 109 pattern classes (that number's still growing), the question arises: how to test them? You could write individual tests for all of them, which is what I started out with. I created a trait called `{php}TestsPatterns` which has one method:

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

This trait significantly reduces the amount of code we need to write: it takes a `{php}Pattern` object, and checks whether a given input returns a matching output.

Good enough? Well, not so fast. Writing tests this way for 109 patterns actually gets pretty boring, pretty fast. I believe that to write a thorough test suite, writing tests need to be as frictionless as possible, otherwise people (me) will just skip writing tests in the long run.

So, I wondered: "can I reduce friction even more?"

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

Now there is even less duplicated setup code to write! However, there is a glaring problem: this approach scales poorly. Can you imagine having all pattern tests in one test file? Luckily, PhpStorm allows to run a single data provider entry, and is able to tell you which specific data provider entry failed:

![](/resources/img/blog/pattern-test/phpstorm.png)

But still… hundreds, potentially thousands, of tests in the same file doesn't seem like an approach that would work in the long run.

Thanks to data providers though, I had another idea. What if there's one "main test" that's responsible for testing all patterns, but what if its data provider entries were aggregated from separate files? 

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

This data provider now:

- Scans all classes in the right directories
- Checks each class for one of more `{php}PatternTest` attributes
- Uses each of those attributes to generate a test

And… that's it! In this case, I find this approach to be very handy. There's also no need to worry about performance overhead: the whole testsuite for `tempest/highlight` runs in 50ms.

The only downside to this approach is that you cannot run a _specific_ pattern test on its own, without having first run the whole testsuite. PhpStorm is able to run data provider entries individually when they are listed within the data provider method, but filling that array dynamically of course prevents PhpStorm from detecting that. 
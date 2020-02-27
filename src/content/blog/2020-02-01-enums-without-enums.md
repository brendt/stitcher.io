Enums are still lacking in PHP, yet there is a clean way to have enum-like behaviour in your code bases, without using  external dependencies. Take the example of [date range boundaries](*/blog/comparing-dates): its boundaries can be included or excluded. Here's how a `Boundaries` enum would be used:

```php
$dateRange = <hljs type>DateRange</hljs>::<hljs prop>make</hljs>(
    '2020-02-01', 
    '2020-03-01', 
    <hljs type>Boundaries</hljs>::<hljs prop>INCLUDE_ALL</hljs>()
);
```

{{ ad:carbon }}

This is what the constructor signature of `DateRange` looks like:

```php
public function __construct($start, $end, <hljs type>Boundaries</hljs> $boundaries);
```

That's the first requirement: **we want to use the type system to ensure only valid enum values are used**.

Next, we want to be able to ask the enum which boundaries are included, like so:

```php
$dateRange->boundaries-><hljs prop>startIncluded</hljs>();
$dateRange->boundaries-><hljs prop>endIncluded</hljs>();
```

This means that each enum value should support its own implementation of `startIncluded` and `endIncluded`. 

That's the second requirement: **we want our enums to support value-specific behaviour**.

On first sight, the easiest solution is to have a `Boundaries` class, and implement `startIncluded` and `endIncluded` like so:

```php
final class Boundaries
{
    private const INCLUDE_NONE = 'none';
    private const INCLUDE_START = 'start';
    private const INCLUDE_END = 'end';
    private const INCLUDE_ALL = 'all';

    private string $value;

    public static function INCLUDE_START(): self
    {
        return new self(self::INCLUDE_START);
    }

    private function __construct(<hljs type>string</hljs> $value) 
    {
        $this->value = $value;
    }

    public function startIncluded(): bool
    {
        return $this->value === self::INCLUDE_START
            || $this->value === self::INCLUDE_ALL;
    }
    
    public function endIncluded(): bool
    {
        return $this->value === self::INCLUDE_END
            || $this->value === self::INCLUDE_ALL;
    }
}
```

In short: using conditionals on an enum's value to add behaviour.

For this example, it's a clean enough solution. However: it doesn't scale that well. Imagine our enum needs more complex value-specific functionality; you often end up with large functions containing large conditional blocks. 

The more conditionals, the more paths your code can take, the more complex it is to understand and maintain, and the more prone to bugs.

That's the third requirement: **we want to avoid using conditionals on enum values**.

In summary, we want our enums to match these three requirements:

- Enum values should be strongly typed, so that the type system can do checks on them
- Enums should support value-specific behaviour
- Value-specific conditions should be avoided at all costs

Polymorphism can offer a solution here: each enum value can be represented by its own class, extending the `Boundaries` enum. Therefore, each value can implement its own version of `startIncluded` and `endIncluded`, returning a simple boolean.

Maybe we'd make something like this:

```php
abstract class Boundaries
{
    public static function INCLUDE_NONE(): IncludeNone
    {
        return new <hljs type>IncludeNone</hljs>();
    }
    
    // …
    
    abstract public function startIncluded(): bool;

    abstract public function endIncluded(): bool;
}
```

And have a concrete implementation of `Boundaries` like this — you can imagine what the other three would look like:

```php
final class IncludeNone extends Boundaries
{
    public function startIncluded(): bool
    {
        return false;
    }

    public function endIncluded(): bool
    {
        return false;
    }
} 
```

While there's more initial work to program these enums, we now meet all requirements.

There's one more improvement to be made. There's no need to use dedicated classes for specific values; they will never be used on their own. So instead of making four classes extending `Boundaries`, we could use anonymous classes:

```php
abstract class Boundaries
{
    abstract public function startIncluded(): bool;

    abstract public function endIncluded(): bool;

    public static function INCLUDE_NONE(): Boundaries
    {
        return new class extends Boundaries 
        {
            public function startIncluded(): bool {
                return false; 
            }

            public function endIncluded(): bool {
                return false; 
            }
        };
    }

    public static function INCLUDE_START(): Boundaries
    {
        return new class extends Boundaries 
        {
            public function startIncluded(): bool {
                return true; 
            }

            public function endIncluded(): bool {
                return false; 
            }
        };
    }

    public static function INCLUDE_END(): Boundaries
    {
        return new class extends Boundaries 
        {
            public function startIncluded(): bool {
                return false; 
            }

            public function endIncluded(): bool {
                return true; 
            }
        };
    }

    public static function INCLUDE_ALL(): Boundaries
    {
        return new class extends Boundaries 
        {
            public function startIncluded(): bool {
                return true; 
            }

            public function endIncluded(): bool {
                return true; 
            }
        };
    }
}
```

Ok, I was mistaken: there were two more improvements to be made. This is a lot of repeated code! But again there's a solution for that! Let's simply define two properties on each value-specific class (`$startIncluded` and `$endIncluded`) and let's implement their getters on the abstract `Boundaries` class instead!

```php
abstract class Boundaries
{
    protected <hljs type>bool</hljs> $startIncluded;
    protected <hljs type>bool</hljs> $endIncluded;
    
    public function startIncluded(): bool 
    {
        return $this->startIncluded;
    }
    
    public function endIncluded(): bool 
    {
        return $this->endIncluded;
    }

    public static function INCLUDE_NONE(): Boundaries
    {
        return new class extends Boundaries 
        {
            protected <hljs type>bool</hljs> $startIncluded = false;
            protected <hljs type>bool</hljs> $endIncluded = false;
        };
    }

    public static function INCLUDE_START(): Boundaries
    {
        return new class extends Boundaries
        {
            protected <hljs type>bool</hljs> $startIncluded = true;
            protected <hljs type>bool</hljs> $endIncluded = false;
        };
    }

    public static function INCLUDE_END(): Boundaries
    {
        return new class extends Boundaries
        {
            protected <hljs type>bool</hljs> $startIncluded = false;
            protected <hljs type>bool</hljs> $endIncluded = true;
        };
    }

    public static function INCLUDE_ALL(): Boundaries
    {
        return new class extends Boundaries
        {
            protected <hljs type>bool</hljs> $startIncluded = true;
            protected <hljs type>bool</hljs> $endIncluded = true;
        };
    }
}
```

The above is my favourite approach to implement enums in PHP. If there's one downside I can think of, it's that they require a little setup work, though I find that this is a small, one-off cost, that pays off highly in the long run.

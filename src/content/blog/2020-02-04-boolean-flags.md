In my [previous post](*/blog/enums-without-enums) I wrote about applying enum patterns in PHP, without native enum support.

In that post, I gave the example of a "date range boundaries" enum, one that represents which boundaries are included in the range, and which are not. It had four possible values:

- `Boundaries::INCLUDE_NONE();`
- `Boundaries::INCLUDE_START();`
- `Boundaries::INCLUDE_END();`
- `Boundaries::INCLUDE_ALL();`

To represent these boundaries, I stored two boolean flags on the enum value classes: `$startIncluded` and `$endIncluded`.

In this post, I want to show another way to store these two boolean flags, using bitmasks.

{{ ad:carbon }}

Here's a quick recap of what (part of) our enum class looked like:

```php
abstract class Boundaries
{
    private <hljs type>bool</hljs> $startIncluded = false;
    private <hljs type>bool</hljs> $endIncluded = false;

    public function startIncluded(): bool 
    {
        return $this->startIncluded;
    }

    public function endIncluded(): bool 
    {
        return $this->endIncluded;
    }

    // …
}
```

In this case, we're using _two_ variables to store _two_ boolean values. 

Them being booleans though, means they can only have one of two values: `true` or `false`; `1` or `0`. Instead of using a whole byte, we only need one bit to store this value.

Hang on though, a whole byte? — It's actually a lot more: 16 bytes to be exact. PHP stores all variables in a structure called a `zval`, which reserves memory not only for the payload, but also type information, bit flags and what not. You can take a look at it [here](*https://github.com/php/php-src/blob/master/Zend/zend_types.h#L302-L328).

Of those 16 bytes, there's 8 reserved per `zval` to store a payload in; that's 64 bits! 

Now, as a precursor, let's make clear that you probably will never need these kinds of micro-optimisations. Boolean bitmasks are often used in game development, compilers and the like, because they are very memory-efficient. In web applications, though, you can be assured you will probably never need them.

Nevertheless, it's a cool, geeky thing to know, and possible in PHP.

So let's store these two flags in one variable.

```
<hljs keyword>abstract</hljs> <hljs keyword>class</hljs> <hljs type>Boundaries</hljs>
{
    <hljs keyword>protected</hljs> <hljs type>int</hljs> $inclusionMask = <hljs textgrey>0b</hljs>00;
}
```

What's happening here? We're making use of the [binary notation](*https://www.php.net/manual/en/language.types.integer.php#language.types.integer.syntax) of integers to easily work with individual bits. If you ever learned about binary systems in school or somewhere else, you know that `0b00` equals 0, `0b01` equals 1, `0b10` equals 2 and `0b11` equals 3. `0b` is a prefix that PHP uses to know you're writing binary, and `00` are the two actual bits.

Now that we've got two bits to work with, it's easy to store two boolean values in them. Let's say that the rightmost bit represents `endIncluded`, and the leftmost bit represents `startIncluded`.

So `0b01` means that the start boundary is not included, while the end boundary is; `0b11` means both are included — you get the gist.

Now that we know how to store data in bits, we still need a way of reading the information in our `startIncluded()` and `endIncluded()` methods: we don't want to program everything in binary.

Here's where [bitwise operators](*https://www.php.net/manual/en/language.operators.bitwise.php) come into play, more specifically the `and` operator.

Take the following two binary values:

```
<hljs textgrey>0b</hljs>0100101;
<hljs textgrey>0b</hljs>1010101;
```

What happens when we apply an `and` operation on both of these values? The result will have all bits set to `1` wherever both bits were `1` in the two original values:

```
<hljs textgrey>0b</hljs>0<hljs type>1</hljs>00<hljs green>1</hljs>0<hljs green>1</hljs>;
<hljs textgrey>0b</hljs><hljs type>1</hljs>0<hljs type>1</hljs>0<hljs green>1</hljs>0<hljs green>1</hljs>;
```

This is the end result:

```
<hljs textgrey>0b</hljs>0000<hljs green>1</hljs>0<hljs green>1</hljs>;
```

Back to our boundaries example. How can we know whether the start is included or not? Since the start boundary is represented by the leftmost bit, we can apply a bitmask on our inclusion variable. If we want to know whether the start bit is set, we simply need to do an `and` operation between the inclusion mask, and the binary value `0b10`.

How so? Since we're only interested in knowing the value of the start boundary, we'll make a mask for that bit only. If we apply an `and` operation between these two values, the result will always be `0b00`, unless the start bit was actually set.

Here's an example where the start bit is `0`:

```
<hljs textgrey>0b</hljs><hljs type>1</hljs>0; <hljs textgrey>// The mask we're applying</hljs>
<hljs textgrey>0b</hljs>0<hljs type>1</hljs>; <hljs textgrey>// The inclusion mask</hljs>

<hljs textgrey>0b</hljs>00; <hljs textgrey>// The result</hljs>
```

And here's one where the start bit is `1`:

```
<hljs textgrey>0b</hljs><hljs green>1</hljs>0; <hljs textgrey>// The mask we're applying</hljs>
<hljs textgrey>0b</hljs><hljs green>1</hljs>0; <hljs textgrey>// The inclusion mask</hljs>

<hljs textgrey>0b</hljs><hljs green>1</hljs>0; <hljs textgrey>// The result</hljs>
```

The end bit will always be `0` in this case, because the mask we're applying has it set to `0`. Hence, whatever value is stored for the end boundary in the inclusion mask, will always result in `0`.

So how to do this in PHP? By using the binary `and` operator, which is a single `&`:

```
<hljs keyword>public</hljs> <hljs keyword>function</hljs> <hljs prop>startIncluded</hljs>(): <hljs type>bool</hljs> 
{
    <hljs keyword>return</hljs> <hljs keyword>$this</hljs>->inclusionMask & <hljs textgrey>0b</hljs>10;
}

<hljs keyword>public</hljs> <hljs keyword>function</hljs> <hljs prop>endIncluded</hljs>(): <hljs type>bool</hljs> 
{
    <hljs keyword>return</hljs> <hljs keyword>$this</hljs>->inclusionMask & <hljs textgrey>0b</hljs>01;
}
```

PHP's dynamic type system will automatically cast the result, `0` or a numeric value, to a boolean. If you want to be more explicit though, you can write it like so:

```
<hljs keyword>public</hljs> <hljs keyword>function</hljs> <hljs prop>startIncluded</hljs>(): <hljs type>bool</hljs> 
{
    <hljs keyword>return</hljs> (<hljs keyword>$this</hljs>->inclusionMask & <hljs textgrey>0b</hljs>10) !== 0;
}

<hljs keyword>public</hljs> <hljs keyword>function</hljs> <hljs prop>endIncluded</hljs>(): <hljs type>bool</hljs> 
{
    <hljs keyword>return</hljs> (<hljs keyword>$this</hljs>->inclusionMask & <hljs textgrey>0b</hljs>01) !== 0;
}
```

---

Let's make clear that you shouldn't be doing this for performance motivations in PHP. There might even be edge cases where this approach would be less optimal, because our inclusion mask can't be garbage collected unless there are no reference anymore to _any_ of the boolean flags.

_However_, if you're working with several boolean flags at once, it might be useful to store them in one variable instead of several, to reduce cognitive load. You could think of "storing the boolean values" as a behind-the-scenes implementation detail, while the public API of a class still provides a clear way of working with them.

So, who knows, there might be cases where this technique is useful. If you have some real-life use cases, be sure to let me know on [Twitter](*https://twitter.com/brendt_gd) or via [e-mail](mailto:brendt@stitcher.io).

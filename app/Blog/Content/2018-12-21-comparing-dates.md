---
title: 'Comparing dates'
next: structuring-unstructured-data
meta:
    description: 'How to reliably compare dates and periods.'
footnotes:
    - { link: /blog/enums-without-enums, title: 'Enums without enums', description: '— I did a followup post, showing another technique' }
    - { link: /blog/combining-event-sourcing-and-stateful-systems, title: 'Bridging the gap between stateful and event sourced sub-systems' }
---

Here's a simple question: 

Does the date range <span class="no-break">"2019-01-01&thinsp;–&thinsp;2019-01-31"</span> contain the date <span class="no-break">"2019-01-31"</span>?

The answer is yes, right?

… Right?

What if the range ends at 10 o'clock, while our test date starts at 11 o'clock?
Now they don't overlap.

How can we reliably compare dates, if there's always a smaller unit of time we might not know about?
There's two solutions.

{{ ad:carbon }}

## Excluding boundaries 

Here a little mathematics refresher, ranges can be written like so:

```txt
[start, end]
```

Obviously, this notation can be applied to date periods:

```txt
[2019-01-01, 2019-01-31]
```

The square brackets indicate that the boundary is included in the range, 
round brackets mean a boundary is excluded:

```txt
(0, 3)
```

This notation tells us this range contains all numbers between 0 and 3, namely `1` and `2`.

Using exclusive boundaries, we can compare dates with 100% certainty of correctness.

Instead of testing whether <span class="no-break">`[2019-01-01, 2019-01-31]`</span> contains the date <span class="no-break">`2019-01-31`</span>,
why don't we test whether <span class="no-break">`[2019-01-01, 2019-02-01)`</span> contains it?

An excluded end boundary allows us to say that "all dates before <span class="no-break">2019-02-01</span>" are contained within this range.
The times of our date and period don't matter anymore, 
we're always sure that a date before <span class="no-break">2019-02-01</span> will fall within our range.

## Ensuring the same precision

While the above solution mathematically works, it gets awkward in a real world context.
Say we want to note "the whole month of January, 2019" as a range.
It looks like this:

```txt
[2019-01-01, 2019-02-01)
```

This is a little counter intuitive, at least it's not the way we humans think about "January".
We'd never say "from January 1, until February 1, with February 1 excluded".

As it goes in programming, we often sacrifice the "common way of human thinking" 
to ensure correctness.

But there _is_ a way to ensure program correctness, _with_ the notation that makes sense to humans:

```txt
[2019-01-01, 2019-01-31]
```

Our problem originated because we weren't sure about the time of the dates we're working with.
My suggestion is to not work around the problem by excluding boundaries, 
but to eliminate it for good.
 
Let's fix the root of the problem instead of working our way around it. 
Shouldn't that always be the mindset of every programmer?

Let me say that again, because it's oh so important:

> Let's fix the root of the problem instead of working our way around it.

The solution? When you're comparing days, make sure you're only comparing days; not hours, minutes or seconds.

When programming, this means you'll have to store the precision of a date range within that range.
It also means you'll have to disallow comparing dates who have different precisions.

What's your opinion? 
Let me know via [Twitter](*https://twitter.com/brendt_gd) or [e-mail](mailto:brendt@stitcher.io).

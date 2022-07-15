PHP is a strange language when it comes to type systems and static analysis. At its start, it was a very dynamic and weakly typed language, but it has been slowly evolving towards a language with a stricter type system, albeit opt-in. Developers can still write dynamic and untyped PHP code if they want to, but more and more people seem to lean towards using PHP's type system regularly.

You can see this trend all throughout the community:

- PHP's internal team has been creating more and more type-system related features in recent years;
- the rise of external static analysis tools like PHPStan, PhpStorm and Psalm; and
- frameworks are more and more relying on stricter types and even embracing third-party static analysis syntax like generics in Laravel.

I personally think this is a good evolution, but I also realise there is still a large group within the community that don't want to use PHP's type system.

{{ cta:mail }}

I've had several discussions with that group over the years, and it never seems that we're on the same page. I lay out my arguments in favour of stricter type systems and static analysis, and as a response I get something like this: _sure, but it's way too verbose to write all those types, it makes my code too strict to my liking, and I don't get much benefit from it._

So when working on my latest video about [the problem with null](https://youtu.be/e0tstsbD4Ro), I came up with yet another way to phrase the argument, in hopes to convince some people to at least consider the possibility that types and static analysis, despite their overhead, can still be beneficial to them.

So, here goes. Attempt number I-lost-count:

My main struggle when writing and maintaining code isn't about what patterns to use or performance optimizations, it isn't about clean code, project structure or what not; it is about uncertainty and doubt: 

- _Will this variable always be an object of interface `X`?_
- _Should I write an extra null check here, to be sure my program won't crash?_
- _What order should I pass these parameters in again?_
- _What kind of data is in this array?_
- _I don't understand what this function does without reading external documentation._

It are those kinds of questions and doubts that I'm bothered by, and it are those kinds of questions that a static analyser answers for me, most of the time.

So no, using a stricter type system and relying on static analysis doesn't slow you down. It increases productivity tenfold. It takes away so much uncertainty and doubt, and I cannot code without it anymore. 

{{ cta:like }}

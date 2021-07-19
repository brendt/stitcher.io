Do you want to make a guess about when I last encountered a `<hljs type>TypeError</hljs>` in one of my projects? To be honest, I can't remember, so it's probably a few years. Coincidentally, I started relying on static analysis a few years ago as well.

I'm fairly certain that I could disable PHP's runtime type checking altogether — if that was a thing — and have a perfectly working codebase. 

Because, here's the thing about runtime type checks: they are a debugging device, not a safety net. Runtime type errors make it easier for us to detect and fix bugs, but the reality is that your code still crashed at runtime. If a type error occurs in production, the end result is the program crashing, nothing you can do about it.

Now, I've written about type systems before ([here](/blog/tests-and-types), [here](/blog/liskov-and-type-safety) and [here](/blog/the-case-for-transpiled-generics)), so I won't reiterate on the benefits of static analysis again. I just want to point out that static analysis has the power to revolutionise the way we write PHP code even more, and opens doors to lots of possibilities.

The downside? We need a community-wide mind shift: many people aren't ready for this way of programming yet. I believe the reason for that has more to do with the lack of practical experience with static analysis instead of problems with static analysis itself.

These are the benefits of a purely staticly analised 

https://www.reddit.com/r/PHP/comments/iuhtgd/ive_proposed_an_approach_to_generics_on_internals/g5pgkbn/

https://www.reddit.com/r/PHP/comments/j65968/ama_with_the_phpstorm_team_from_jetbrains_on/g7zg9mt/

Downsides:
    - No more runtime type casting, that's a price I'm fine paying

Upsides:    
    - Runtime performance
    - Safer code
    - Easier development
    - Things like generics would suddenly be possible

Options:
    - PHP ships a built-in, static type checker 
        - Rasmus quote
        - Nikita quote
    - Using third party static analysis
        - Possible today
        - Won't work unless endorsed by offcial PHP
    - Transpiling PHP   
        - Success of TS
        - A wide world of possibilities
        - Ecosystem is a requirement

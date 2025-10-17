_Note: this post was first published as part of PHP's "[24 Days in December](https://24daysindecember.net/2022/12/01/all-i-want-for-christmas/)" Advent event._

Let's set aside all practical concerns for a moment — it's Christmas, after all. If you could choose — freely choose: what would you change about PHP? 

Would you want generics or the pipe operator? Maybe you'd like to see consistent function signatures or get rid of the dollar sign. Type aliases, scalar objects, autoloading for namespaced functions, improved performance, less breaking changes, more breaking changes — the list goes on.

But what if I told you, you had to pick one, and only one. What would it be?

My number one feature isn't in this list. Even worse: my number one wish for PHP will probably never happen. But like I said at the beginning: let's set aside all practical concerns. Let's dream for a moment, not because we believe all dreams come true; but because dreams, in themselves, are valuable and give hope — it _is_ Christmas, after all.

---

Let me tell you the story of another programming language. A language that, just like PHP, had been the underdog for decades. It didn't have sexy syntax, it was a poor performer, it had messy methods and confusing classes. It was… JavaScript.

It was a language that — legend says — was written in two weeks. Yet it grew to be the most popular programming language people had ever seen, almost by accident.

Indeed, for years, developers were stuck with JavaScript: it was the only option to do any kind of frontend programming at all. And despite everyone mocking poor JavaScript, there simply were no other alternatives, so they had to use it.

That is: until a bunch of smart people started thinking out of the box. The idea was simple: what if we don't write JavaScript anymore, but instead write a program in another language, and convert that code to JavaScript? That way our programs could still work in the browser, but we didn't have to put up with JavaScript's ugliness and quirkiness.
 
And so history was written: we compiled languages like C to a subset of JavaScript — an optimised subset that was much more performant. It was called asm.js. It allowed us to run existing game engines in the browser. We compiled JavaScript to JavaScript with Babel: newer syntax that wasn't available in browsers was "transpiled" to older JavaScript versions. Supersets like CoffeeScript came into existence. They added better and more convenient syntax. 

More and more, the JavaScript community transformed into a host of languages, with JavaScript simply being a compilation target.

Sure, compiling JavaScript meant adding a build step, but it seemed like developers got used to it. First and foremost: build steps were optimised for performance, and second: you could suddenly add a lot of static functionality to your language: tasks that were performed only when compiling your program but not while running it. TypeScript was created, and static type checking became a thing. It turned out: computers were awfully good at detecting mistakes, as long as we provide them with just enough information — types. 

And everyone marvelled at JavaScript: it grew from a small frontend scripting language to the number 1 programming language in the world. 

---

Let's talk about PHP. I've been writing it for more than a decade now. I love the PHP community, I love the ecosystem, I love how the language has managed to evolve over the years. At the same time I believe there's room for PHP to grow — lots of room. And so I dream. 

I dream of a world where PHP follows in JavaScript footsteps. Where it becomes more than just PHP. I dream of a TypeScript for PHP: a language that's still 100% compatible with all PHP code out there, but one that adds generics and proper static type checking — without having to worry about runtime performance costs. I dream of a language that has all (or most) modern-day language features, that are compiled to plain, old, boring — but working — PHP.

But dreams seldom come true. 

Someone once said: "For JavaScript to become as popular as it is today, there had to be two things: it had to suck, and it had to be the only viable option available". And here's the thing: unlike JavaScript, PHP isn't the _only_ option available for backend programming. I also dare to say that it's way better than JavaScript back in the day. These two main components that were needed for JavaScript to grow into something more than itself, aren't equally present in PHP. 

And that's ok. It means my dream is probably unrealistic, but it also means something much more important.

My realisation recently is that PHP is already awesome. People are already building great things with it. Sure, maybe PHP is boring compared to the latest and greatest programming languages, and sure you might need to use another language if you're building something for those 0.1% of edge cases that need insane performance. My dream of a superset of PHP might be one of many approaches, but it sure isn't the only viable path forward.

Even without that dream of mine: PHP is doing great. It's not because of how its designed, it's not because of its syntax. It's not because of its top-notch performance and it's not because of its incredible type system. It's because people like _you_ are building amazing things with it. Whether you're using PHP 8.2 or not; whether your running it serverless or asynchronous or not; whether you're writing OOP, FP, DDD, ES, CQRS, Serverless or whatever term you want to throw at it — _you_ are building awesome things. It turns out a language is rarely the bottleneck, because it's merely a tool. But "PHP" is so much more than just a language, it's so much more than just a tool. That's because of _you_.

Thank you for being part of what makes PHP great. Happy holidays.

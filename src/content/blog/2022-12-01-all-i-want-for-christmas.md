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

Let's talk about PHP. I've been writing it for more than a decade now. I love the PHP community, I love the ecosystem, I love how the language has managed to evolve over the years. 

But let's be honest: PHP isn't the most sexy language out there. It's not the best performer, it weird and quirky in places, it's average at best. And that's ok: we manage to create amazing things with it because the language itself rarely is the bottleneck.

And yet… I dream. I dream of a world where PHP follows in JavaScript footsteps. Where it becomes more than just PHP. I dream of a TypeScript for PHP: a language that's still 100% compatible with all PHP code out there, but one that adds syntax like generics and the pipe operator, without having to worry about runtime performance costs. A language that has all (or most) modern-day language features, that are compiled to plain, old, boring — but working — PHP.

I dream, but I know how unlikely it is to happen. A wise man once said: "For JavaScript to become as popular as it is today, there had to be two things: it had to suck, and it had to be the only viable option available".

And here's the thing: unlike JavaScript, PHP isn't the _only_ option available for backend programming. I also dare to say that it sucks less than JavaScript back in the day. These two main components that were needed for JavaScript to grow into something more than itself, aren't equally present in PHP. 

Unfortunately for me, PHP is good enough. People are building amazing things with it. The language itself is evolving in a good way. Maybe I don't agree with everything internals decide, but it _is_ evolving. PHP has a great ecosystem, it's performant enough these days, and you can run it serverless or on asynchronous servers if you need to.

For most projects, good enough is good enough. And yet, I dream.

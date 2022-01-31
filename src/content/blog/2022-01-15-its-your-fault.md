I read an intriguing blog post yesterday. It's on old one dating back to 2008, titled "[Even if a function doesn’t do anything, you still have to call it if the documentation says so, because it might do something tomorrow](https://devblogs.microsoft.com/oldnewthing/20080925-00/?p=20763)". That's quite a mouthful, but the article itself is rather short; I can summarise it one paragraph for you.

There used to be a function in Windows Kernel that didn't do anything. Yet, the docs told programmers that this function _had_ to be called after calling another one: _if_ you called `<hljs prop>GetEnvironmentStrings</hljs>`, you _also_ needed to call `<hljs prop>FreeEnvironmentStrings</hljs>`. However, many programmers didn't bother to do so because it was pointless: `<hljs prop>FreeEnvironmentStrings</hljs>` didn't do anything, it was literally an empty function. A couple of years later, that function actually got an implementation, and many applications started to break because their programmers never bothered to call it in the first place.

The article summarises it as follows:

> If the documentation says that you have to call a function, then you have to call it. It may be that the function doesn’t do anything, but that doesn’t prevent it from doing something in the future.

Or, how I like to phrase it: some of the most crappy software design possible. 

There was a whole debate in the [comment section](https://web.archive.org/web/20100222121715/http://blogs.msdn.com/oldnewthing/archive/2008/09/25/8965129.aspx) on whether Microsoft or the developers — the users of Microsoft's code — messed up. After all: the docs explicitly told their users they needed to call that function, so if they didn't follow the rules, they were on their own.

There were a couple of commentators calling out Microsoft though:

> Why not instead design the API such that the programmer cannot fail to use it correctly and still have their program compile?

<em class="small center">
    <a href="https://web.archive.org/web/20100222121715/http://blogs.msdn.com/oldnewthing/archive/2008/09/25/8965129.aspx#8965837">Source</a>
</em>

And:

> As a sometimes systems-programmer myself, I'm dismayed that it took until just a couple of comments ago before somebody pointed out that it was ALSO a dumb thing to stick in a do-nothing function call and then assume people would call it by contract.
>
> Uh, did that REALLY seem like a good idea to anybody? By Windows NT 4, had we not all figured out a long time ago that many application developers are not going to do things exactly the way you tell them?
>
> This is MS's error first; the app developer's second. Neither one is right, but MS was more wrong.

<em class="small center">
    <a href="https://web.archive.org/web/20100222121715/http://blogs.msdn.com/oldnewthing/archive/2008/09/25/8965129.aspx#8966943">Source</a>
</em>

In my opinion, these are some sensible arguments. Users in general can't be trusted to, one, read the docs and two, follow rules that don't seem to make sense at that point in time. That's just not how to world works.

But ok, this was 2008, we've learned to do better now, don't we? Well… it's actually still very common to write code that's going to be used by others, and _assume_ those users will know how to use that code responsibly. I personally know plenty of people who follow this mindset. I respect them very much, and they also know I disagree with them on this opinion.

Looking at it from the user perspective though, this mindset is suboptimal as well: if the code _itself_ isn't clear on how it should be used, there's a level of uncertainty introduced in the user's mind. "Am I missing something here?" "Should I read every docs page available before actually using this code?"

I think it's better software design, for vendors and users alike, to make our code as explicit and robust as possible, with as little room for interpretation and uncertainty as possible.

In my opinion, that means:

- Using a proper type system, as strict as possible
- Not allowing classes to be extended, unless it's by design, `<hljs keyword>final</hljs>` by default
- Not allowing state to be writeable from the outside, unless it's by design, `<hljs keyword>readonly</hljs>` by default
- Only adding methods to your public API that _actually_ should be publicly accessible, `<hljs keyword>private</hljs>` by default
- Using explicit, clear names everywhere
- Programing to an interface instead of an implementation

> It's better software design, for vendors and users alike, to make our code as explicit and robust as possible, with as little room for interpretation and uncertainty as possible.

To me, it's not a matter of distrust, it's about writing code that's clear about what it does, without having to dig through a set of rules written in the docs somewhere far away from your IDE. 

There's only very little code around these days that doesn't need extra explanation, and I think we can do better than that. 

Hi ::subscriber.first_name::

I reckon not many people know this of me, but my programming career started as a frontend developer. Well, of course, that depends on whether you consider "HTML and CSS" to be "programming languages", but I would say _yes_ — they are. I actually really liked CSS and only got into "proper" programming because of my school's education. Sure, I liked languages like Java and PHP as well, but CSS has always kept a special place in my heart.

Over the years, though, my CSS skills rusted. I try to keep up with the latest developments "from afar", but I wouldn't call myself a frontend engineer in any way anymore. The only thing I still feel comfortable with these days is to add some Tailwind classes to my markup and then hope someone else will improve my work 😅

I'm coming to a painful realization, though; because of Tailwind, I don't know how to write CSS anymore. It's a weird situation that I've worked myself into. Ask me how to make a flexbox or grid design with the right alignment options, I can come up with it in Tailwind pretty easily. Ask me to do it in CSS, and I'm blank. I know of `display: flex;`, but that's about it. And sure, I can relearn CSS; that's not the issue. It's rather that I've become so comfortable using a tool that "compiles to CSS" that I don't know the underlying language anymore. 

I think part of the problem is that there is no one-to-one mapping between Tailwind classes and CSS properties. There are small differences in names that make me feel "stuck" with Tailwind. There's `.row-start-<number>` while the CSS property is called `grid-row-start`; the `.flex-auto` class has a `flex` prefix, but the `.grow` class doesn't, even though it maps to a property called `flex-grow`. It's `.justify-items-end` but then it's `.items-end` instead of `.align-items-end`, `.font-bold` instead of `.font-weight-bold`, `.leading` instead of `.line-height`; and so on.

I'm sure there are reasons why Tailwind has chosen this naming convention, but the problem I'm now facing is that when I write normal CSS, I don't know the right property names or values anymore. There's a reason I stick with Tailwind, of course. I really like its component-based approach: CSS always becomes messy and I don't think there's any way to truly prevent that. At least Tailwind's approach keeps the mess somewhat contained in the right place. So yeah, I've come to like Tailwind because of how it keeps CSS and HTML together, which is really powerful combined with component-based frontend design. But then there's the unfortunate side effect of Tailwind becoming a language on its own. Everything feels like CSS, everything feels natural; until you have to write real CSS and realize it's not. That's when I realized I had been vendor-locked.

I think my wish is that there was something like Tailwind, but then with a one-to-one mapping to normal CSS. That way I could keep my component-based CSS with my components — where it belongs — I could sprinkle some custom or "global" CSS on top of that where needed, all while using the same language. This comes awfully close to inlining CSS styles, but I'd say there's still a slight difference, although I couldn't really tell you what. Maybe I should give it a try?

Or maybe… you have the perfect solution that will help me put this internal struggle to rest? Let me know!

## In other news

Apart from sharing my CSS struggles with you, I also wanted to share a personal win: a couple of weeks ago, I published my new book [Things I wish I knew when I started programming](https://things-i-wish-i-knew.com/). I'm proud that I've managed to sell over 50 copies of it by know. I know I'm far from being a best-selling author, but I went into this project without any expectations, so 50 sales is a win for me!

I have to admit, I'm dreaming of an even bigger win. So maybe you were still on the fence on whether you'd want to buy a copy or not? For all my newsletter subscribers, I've created a coupon so that you can buy the book for $7.50 instead of the suggested $19.00 on Leanpub! Feel free to ignore it if you don't want it, but if you do, here's your chance: [https://leanpub.com/things-i-wish-i-knew/c/stitcher-mail](https://leanpub.com/things-i-wish-i-knew/c/stitcher-mail).

Until next time!

Brent
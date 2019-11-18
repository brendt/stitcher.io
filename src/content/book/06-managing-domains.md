In the previous chapters we looked at the three core building blocks of our domains: DTOs, actions and models. Today we take a breather from the low level technical stuff, and focus on the philosophical side: how do you start using domains, how to identify them, and how to manage them in the long run?

{{ ad:carbon }}

## Teamwork

At the start of this series I claimed that all paradigms and principles I wrote about would serve a purpose: to help teams of developers in keeping their larger-than-average Laravel applications maintainable over the years.

Some people voiced their concern: wouldn't a new directory structure and the use of complex principles make it difficult for new developers to join right away?

If you're a developer acquainted with default Laravel projects, how they are taught to beginners; it's true that you'll need to spend some time learning about how these projects are handled. However, this is not as big a deal as some people might think. 

Imagine a project with around 100 models, 300 actions, almost 500 routes. This is the scale of projects I'm thinking about. The main difficulty in these projects is not how the code is technically structured, rather it's about the massive amount of business knowledge there is to grasp. You can't expect new developers to understand all of the problems this project is solving, just in an instant. It takes time to get to know the code, but more importantly: the business. The less magic and indirections there are, the less room there is for confusion.

It's important to understand the goal of the architecture I'm unfolding in this series: it's not about writing the shortest amount of characters, it's not about the elegance of code; it's about making large codebases more easy to navigate, to allow as little room as possible for confusion and to keep the project healthy for long periods of time.

We actually have experience with this process in practice: having worked  with a team of three developers on one of our projects, a new backend developer joined, colleague Ruben.

The architecture was new to him, even if he had experience with Laravel before. So we took the time to guide him through. After only a few hours of briefing and pair programming, he was already able to work in this project independently. It definitely took several weeks to get a thorough understanding of the scope of the project, but fortunately the architecture didn't stand in his way — on the contrary: it helped Ruben to focus on the business logic instead.

If you made it until this point in the blog series, I hope that you understand that this architecture is not meant to be the golden bullet for every project. There are many cases where a simpler approach could work better, and some cases where a more complex approach is required.

## Identifying domains

With the knowledge we now have about the basic domain building blocks, the question arises how exactly we start writing actual code. There are lots of methodologies you can use to better understand what you're about to build, though I feel that there are two key points:

- Even though you're a developer, your primary goal is to understand the business problem and translate that into code. The code itself is merely a means to an end, always keep your focus on the problem you're solving.
- Make sure you've got face-to-face time with your client. It will take time to extract the knowledge that you require to write a working program.

I came to think of my job description more and more as "a translator between real world problems, and technical solutions", instead of "a programmer who writes code".
I firmly believe that this mindset is key if you're going to work on a long-running project. You don't just have to write the code, you need to understand the real-world problems you're trying to solve.

Depending on the size of your team, you might not need face-to-face interaction between _all_ developers and the client, still all developers will need to understand the problems they are solving with code. 

These team dynamics are such a complex topic that they deserve their own book. In fact there's a lot of literature out there specifically on this topic. For now I'll keep it at this, because from here on out we can talk about how we translate these problems into domains.

In chapter 1, I wrote that one of the goals of this architecture is to group code that belongs together, based on their meaning in the real world, instead of their technical properties. If you've got an open communication with your client, you'll note that it takes time — lots of time — to get a good idea of what their business is about. Often your client might not know it exactly themselves, and it's only by sitting down that they start thoroughly thinking about it.

That's why you shouldn't fear domain groups that change over time. You might start with an `Invoice` domain, but notice half a year later that it has grown too large for you and your team to fully grasp. Maybe invoice generation and payments are two complex systems on their own, and so they can be split into two domain groups down the line.

My point of view is that it's healthy to keep iterating over your domain structure, to keep refactoring it. Given the right tools, it's not difficult at all to change, split and refactor domains; Your IDE is your friend! My colleague Freek took the time to record a practical example in which he refactors a default Laravel application to the architecture described in this series. You can take a look at his live refactoring session over here.

<p>
<iframe width="560" height="315" src="https://www.youtube.com/embed/yPiMzw-lLF8" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
</p>

I summary: don't be afraid to start using domains, you can always refactor them later.

So that's the approach I would take if you want to start using this domain oriented architecture: try to identify subsystems within your project, realising they can — and will — change over time. You can sit down with your client, you can ask them to write some things down, you can do event storming sessions with them. Together you form an image of what the project should be, and that image might very well be refined and even changed down the road.

And because our domain code has very little dependencies, it's very flexible, it doesn't cost much to move stuff around or refactor your code.

---

Are you enjoying this series this far? Got any questions or feedback? Feel free to let me know via [Twitter](*https://twitter.com/brendt_gd) or [e-mail](mailto:brendt@stitcher.io). 

Next week we'll dive back into code, and finally arrive at the application layer, looking forward to it!

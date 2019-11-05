In the previous chapters we looked at the three core building blocks of our domains: DTOs, actions and models. Today we take a breather from the low level technical stuff, and focus on the philosophical side: how do you start using domains, how to identify them, and how to manage them in the long run?

{{ ad:carbon }}

## Teamwork

At the start of this series I claimed that all paradigms and principles I wrote about would serve a purpose: keeping your larger Laravel applications maintainable over the years, in large teams.

Some people voiced their concern: "wouldn't a whole new directory structure and the use of complex principles make it difficult for new developers to join right away?"

If you're a Laravel developer experienced with the default Laravel way and how it's taught to beginners, it's true that you'll need to spend some time learning about how these projects are handled. However, this is not as big a deal as some people might think. 

Imagine a project with around 100 models, 300 actions, almost 500 routes. This is the scale of projects we're talking about. The main difficulty in these projects is not the how the code is technically structured, rather it's about the massive amount of business knowledge there is to grasp. You can't expect new developers to just understand all of the problems this project is solving, just in an instant. It takes time to get to know the code. The less magic and indirections there are, the less room there is for confusion.

It's important to understand the goal of the architecture I'm unfolding in this series: it's not about writing the shortest amount of characters, it's not about the elegance of code; it's about making huge amounts of code as simple as possible, to allow as little room for confusion as possible and keep the project healthy for long periods of time.

We actually have real life experience with this approach. After working with a team of three developers on one of our projects, a new backend developer joined: colleague Ruben.

The architecture was new to him, even if he had experience with Laravel before. We took the time to guide him through it. After only a few hours of briefing and pair programming, he was already able to work in this project independently. It definitely took several weeks to fully grasp the scope of the project, but fortunately the architecture didn't stand in his way — on the contrary: its simplicity helped Ruben to focus on the business logic instead.

## Identifying domains

With the knowledge we now have about the basic domain building blocks, the question arises how exactly we start writing actual code. There are lots of methodologies you can use to better understand what you're about to build, though I feel that there are two most important points:

- Even though you're a developer, your primary goal is to understand the business problem and translate that into code. The code itself is merely a means to an end, always keep your focus on the problem you're solving.
- Make sure you've got face-to-face time with your client. It'll take time to extract the knowledge that you require to write a working program.

I came to think of my job description more and more as "a translator between real world problems, and technical solutions", instead of "a programmer who writes code".

I firmly believe that this mindset is key if you're going to work on a long-running project. You don't just have to write the code, you need to understand the real-world problems you're trying to solve.

Depending on the size of your team, you might not need face-to-face interaction between the client and all developers, still all developers will need to understand the problems they are solving. 

These team dynamics are such a complex topic that they deserve their own book. In fact there's a lot of literature out there specifically on this topic. For now I'll keep it at this, because from here on out we can talk about how we translate these problems into domains.

In chapter 1 I wrote that one of the goals of this architecture is to group code that belongs together, based on their meaning in the real world, instead of their technical properties. If you've got an open communication with your client, you'll note that it takes time — lots of time — to get a good idea of what their business is about. Often your client might not know it exactly themselves, and it's only by sitting down with you that they start thoroughly thinking about it.

That's why you shouldn't fear domain groups that change over time. You might start with an `Invoice` domain, but notice half a year later that it has grown too large for you and your team to fully grasp. Maybe invoice generation and payments are two complex systems on their own, and so they can be split into two domain groups down the line.

I'd say it's healthy to keep iterating over your domain structure. It's not even hard to make change, split and refactor domains, given the right tools. Your IDE is your friend! My colleague Freek took the time to show you a practical example, you can take a look at a live refactoring session over here:

<p>
<iframe width="560" height="315" src="https://www.youtube.com/embed/yPiMzw-lLF8" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
</p>

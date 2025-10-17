---
title: 'The IKEA effect'
meta:
    description: 'About the value of our own creations'
    image: resources/img/blog/ikea/meta.png
footnotes:
    - { link: /blog/builders-and-architects-two-types-of-programmers, title: 'Dealing with different personalities within your team' }
    - { link: /blog/a-programmers-cognitive-load, title: "A programmer's cognitive load" }
    - { link: 'https://excalidraw.com/', title: 'Illustrations made with Excalidraw' }
author:
    name: 'Dimitris Karapanos'
    url: 'https://twitter.com/gpanos00'
---

## The spark

A few days ago while I was browsing the forums, I stumbled across a discussion that was the spark for this article. 
In short the OP was having problems with an expensive query and trying his best to optimize it. 
The thread had a lot of responses suggesting ways of optimizing the query like, index this column, use sub selects instead of joins, chunk the results etc…

What was weird for me was the fact that the OP was trying to load 13k options into a select element. I tried to suggest him that even if he manages to optimize the query to a few milliseconds the user experience would be less than poor. I spent time making my case presenting him with alternatives, articles with ux best practices and mobile device optimizations. The focus didn't change. The dedication to improve his existing solution was absolute. Needless to say that the thread is still open to this day…

{{ ad:carbon }}

## The revelation

The whole thing made me think how many times I encountered this behavior in my working environment including myself. I tried to find a real life example that simulates this behavior while drinking my early morning coffee. I stretched and carefully rested my cup to my poorly constructed coffee table. I purchased this table 2 years ago from IKEA and spend hours trying to build it only to realise at the end that I was left with one extra screw. I messed up and skipped a part of the instructions but I was done. I wasn't gonna build the whole thing again for one screw. 

To this day my coffee table is supported by a string attached to the legs and all my friends visiting know that the table needs to be treated gently.

## The bummer

I immediately came up with the name "The IKEA effect" and I congratulated myself for the coolness of it. Sure enough, after googling it I realized that yep that was a thing: there is a very interesting paper from Michael I. Norton, a professor in Harvard, titled [The “IKEA Effect”: When Labor Leads to Love](*https://www.hbs.edu/faculty/publication%20files/11-091.pdf) and to sum it up here is the wikipedia definition: 

> The IKEA effect is a cognitive bias in which consumers place a disproportionately high value on products they partially created

<div class="image-noborder"></div>

![](/img/blog/ikea/1.png)

## The IKEA Effect revisited

In my career as a programmer I noticed this phenomenon's negative side effects quite often. It manifests itself in different ways and in different levels of an organization. 

From the top level management dedicating time and effort to define the next big thing only to get grounded by the technical constrains or the level of effort required to build it. From the designer spending days to come up with a high fidelity design that at the end gets negative feedback and has to go back to the drawing board. Finally for the programmer who gets a code review after spending days working on a feature that basically requires rewriting the whole thing! 

## The bigger the effort the greater the blindness

When working on a solution to a problem, the deeper you go, your focus switches to specifics parts of the problem. Many times those parts might require a disproportional amount of effort in order to solve them. 

How many times I found myself searching and furiously going through stackoverflow to make this one thing work! When I finally got this little piece of the solution working, I was so attached to it that I wouldn't even consider the thought that it might not be the right way to do it. 

I was ready to defend its honor against anyone who dared to challenge it! At this stage the feedback in any kind or form — either this comes from code review or over the water cooler — the result is tension. The worst part is that the feedback is probably right, but you are already too blind to see or accept it.

## The assembly manual

So how do we stop investing more and more into what we have already worked on, rather than striving for better or more efficient alternatives?

This are my takeaways.

**Read the instructions**: don't jump into the solution without understanding the problem. Invest time and ask for clarifications when needed.

**Early feedback**: write a summary of your proposed solution and communicate it to your colleagues, make sure that everybody that needs to be involved is aware. 

**Build a draft**: code it, make it work, don't think about abstractions and future maintainability, just make it as simple as possible. Again ask for feedback and iterate.

**Rebuild it**: you've got some feedback that requires you to heavily refactor? Big deal you were going to rewrite the thing anyhow, no hard feelings. 

**Know when to stop**: if something doesn't feel right, gets weirdly complicated to model it in your solution, then it smells bad. Stop and revisit your approach better now than later. Do you find yourself lost? Struggling to find a way to make it work? Maybe the problem is too hard for your level of expertise. There is no shame in this. Communicate it early with your senior experts ask for tips, pair coding, anything that can help you grow your game to be able to fight the beast.

**Wrap it up**: don't try to make every little part perfect. Pay attention to the core of the solution and learn to live with the rest. Ship it and after some time when you are emotionally detached revisit and refactor.

---

Once again thanks to [Dimitris](*https://twitter.com/gpanos00), be sure to check him out! If you're interested in discussing your thoughts in this topic, you can share them [on HN](*https://news.ycombinator.com/item?id=23256032).

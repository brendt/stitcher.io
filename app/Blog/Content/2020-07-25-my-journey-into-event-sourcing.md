---
title: 'My journey into event sourcing'
next: combining-event-sourcing-and-stateful-systems
meta:
    description: 'A collection of talks that have guided me while learning event sourcing'
---

In this post I want to share four talks that have guided me into the world of event driven development, and by extent into event sourcing.

I wanted to share these talks here on my blog, because I figured some of you might be interested in them, and this way I can revisit them in the future.

---

Starting with Martin Fowler, who explains the basics of event driven development, the pros and cons, as well as the different patterns that can be applied on top of EDD, one of them event sourcing.

<p>
    <iframe width="560" height="400" 
        src="https://www.youtube.com/embed/STKCRSUsyP0" 
        frameborder="0" 
        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" 
        allowfullscreen>
    </iframe>
</p>

---

Next Greg Young, one of the founding fathers of event sourcing and CQRS. What's most interesting in this talk is the misconceptions Greg talks about. The one that stood out to me most, is that event sourcing isn't a top-level architecture, it's a pattern that should be applied in parts of your projects where relevant. A great insight, one that has guided us throughout [our latest project](/blog/combining-event-sourcing-and-stateful-systems). 

<p>
    <iframe width="560" height="400" 
        src="https://www.youtube.com/embed/LDW0QWie21s" 
        frameborder="0" 
        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" 
        allowfullscreen>
    </iframe>
</p>

{{ ad:carbon }}

---

Next up an old talk be Eric Evans. I know the video and sound quality is crap, but the way he talks about splitting large systems in small pieces is awesome. 

The greatest insight for me is how he explains the concept of micro services within the context of one large system. Eric explains concrete ways of dealing with such a split system, which directly ties in the point made by Greg Young earlier: event sourcing should only be applied in parts of your system. Eric gives us concrete strategies of doing that. 

<p>
    <iframe width="560" height="400" 
        src="https://www.youtube.com/embed/OTF2Y6TLTG0" 
        frameborder="0" 
        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" 
        allowfullscreen>
    </iframe>
</p>

---

Finally, putting everything into practice with code: Freek shows a hands-on integration of event sourcing into a Laravel projects, the framework I also work with daily.

<p>
    <iframe width="560" height="400" 
        src="https://www.youtube.com/embed/9tbxl_I1EGE" 
        frameborder="0" 
        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" 
        allowfullscreen>
    </iframe>
</p>

---

{{ cta:mail }}

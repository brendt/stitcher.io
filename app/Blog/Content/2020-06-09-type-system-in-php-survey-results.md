---
title: 'Survey results: type systems in PHP'
next: new-in-php-8
meta:
    description: 'How does type system usage compare to team and project size?'
    image: resources/img/blog/survey/meta.png
footnotes:
    - { link: /blog/tests-and-types, title: 'Thoughts on strong and weakly typed programming languages' }
    - { link: /blog/new-in-php-8, title: "What's new in PHP 8" }
    - { link: /blog/the-latest-php-version, title: 'The latest PHP version' }
---

I use PHP's type system as much as possible, though often found resistance with the people I interact with on Twitter and Reddit.
After having discussed the topic numerous times, I felt like both "camps" were not really listening to each other, or at least not understanding each others point. 

It made me wonder if and how the team we work in, and the kind of projects we work on, might influence our view of type system usage. 

I decided to do a little survey, and gather some actual insights in the topic.

{{ ad:carbon }}

Fair warning: I'm no neutral player in this discussion, but I want to make clear that it wasn't my intention to make my own case. I wanted to see whether our seemingly pointless discussions might be caused by the difference in context; I don't want to prove there's one and only true answer.

With all that being said, let's look at the results.

---

First of all I'd like to thank all 686 people who participated in this survey. I realise this is a small group, though I hope it's representative enough to draw some conclusions. If you think that the results aren't accurate enough, please reach out to me to discuss whether and how we can redo this survey on a larger scale.

Based on the answers in the survey, I made five groups of profiles: `A`, `B`, `C`, `D` and `E`. `A` and `B` lean (strongly) towards a stricter type system, `C` is somewhat neutral, and `D` and `E` lean (strongly) towards not using type systems.

This "type profile" was determined by mapping the answers to relevant questions to a score: 1 and 2 points were given to answers favorable to strict type systems, 0 to neutral answers and -1 and -2 to answers leaning towards no type systems.  

<div class="image-noborder"></div>

![](/resources/img/blog/survey/1.png)

<em class="center small">Type profile of all participants</em>

One thing that immediately stood out is the large amount of people who lean towards the use of a strict type system. I did not expect this. From discussions I had on Twitter, I had the feeling that more people would be in group `C`, `D` or `E`.

These are some of the most popular arguments against the use of PHP's type system, at least the ones I heard in my discussions:

- PHP's type system still fails at runtime, so there's no advantage to using it
- Types add unnecessary visual overload
- The flexibility of using PHP's type juggling is preferred 

Of course this survey wanted to examine whether there's a correlation between personal preference and team- and project size. Let's look at team size first.

This chart shows the average team size, and for each group the distribution of type profiles within that group.

<div class="image-noborder"></div>

![](/resources/img/blog/survey/2.png)

<em class="center small">Type profile distribution, grouped by team size</em>

We'd need to look at relative results to test whether there's a correlation or not. So here goes, but keep in mind that the group with `2-10 people`, is by far the largest.

<div class="image-noborder"></div>

![](/resources/img/blog/survey/3.png)

<em class="center small">Relative type profile distribution, grouped by team size</em>

As I expected, based on discussions: profiles `D` and `E` are more present in smaller teams. Yet I admit I expected that group to be larger again.

Next I looked at project size. I asked participants to describe the size of an average project they work on: small, medium, large or extra large.

<div class="image-noborder"></div>

![](/resources/img/blog/survey/4.png)

<em class="center small">Relative type profile distribution, grouped by project size</em>

This chart shows a growth of type `A` and `B`, related to the size of the project. Most times, "project size" also translates to "project duration", which is why I also asked participants to rate the project duration of such an average project. 

<div class="image-noborder"></div>

![](/resources/img/blog/survey/5.png)

<em class="center small">Relative type profile distribution, grouped by project duration</em>

Again we see a preference for stricter type systems in longer projects, but we should of course be aware that there were less participants in these groups. Furthermore, I found it interesting that in this case, there' no linear pattern to discover, as with the previous charts.

## Conclusions

Unfortunately, I think there weren't enough participants distributed across all kinds of projects and team sizes to draw final conclusions here. 

Up front, I assumed that the group who preferred not to use type systems would have been larger; but maybe it's simply a more vocal group, even though smaller? I can't say that for sure though.

I do think that, even with a small amount of participants, we can assume there is a correlation between type system usage and project- and team size; but ideally, we'd need a larger participant pool.

My personal takeaway is that when entering type system discussions, we should be wary to compare each others preference: there might be a good case that you're simply working in a completely different kind of project, and there's no way of telling who's right or wrong.

{{ cta:mail }}

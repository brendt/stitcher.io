I'm a proponent of using PHP's type system as much as possible, though often found resistance with the people I interact with on Twitter and Reddit.

After discussed the topic numerous times, I felt like both "camps" were not really listening to each other, or at least not understanding each others point. 

It made me wonder if and how the team we work with, and the kind of projects we work on, might influence our view of type system usage. 

I decided to do a little survey, and gather some actual insights in the topic.

{{ ad:carbon }}

Fair warning: I'm no neutral player in this discussion, but I want to make clear that it wan't my intention to make my own case. I wanted to see whether our seemingly pointless discussions might be caused by the different kinds of projects and teams, and not to prove there's one and only true answer.

With all that being said, let's look at the results.

---

First of all I'd like to thank all 686 people who participated in this survey. I realise this is a small group, though I hope it's representative enough to draw some conclusions. If you think that the results aren't accurate enough, please reach out to me to discuss whether and how we can redo this survey on a larger scale.

Based on the answers in the survey, I made five groups of profiles: `A`, `B`, `C`, `D` and `E`. `A` and `B` lean (strongly) towards a stricter type system, `C` is somewhat neutral, and `D` and `E` lean (strongly) towards not using type systems.

This "type profile" was determined by mapping the answers to relevant questions to a score: 1 and 2 points were given to answers favorable to strict type systems, 0 to neutral answers and -1 and -2 to answers leaning towards no type systems.  

<div class="image-noborder"></div>

![](/resources/img/blog/survey/1.png)

<em class="center small">Type profile of all participants</em>

One thing that immediately stood out is the large amount of people who lean towards the use of a strict type system. I did not expect this. From discussions I had on Twitter, I had the feeling that more people would lean towards not using PHP's type system.

These are some of the most popular arguments against the use of PHP's type system, at least the one I heard in my discussions:

- PHP's type system still fails at runtime, so there's no advantage to using it
- Types add unnecessary visual overload
- The flexibility of using PHP's type juggling is preferred 

Of course this survey wanted to examine whether there's a correlation between personal preference and team- and project size. Let's look at team size first.

This chart shows the average team size, and for each team size the distribution of type profiles within that group.

<div class="image-noborder"></div>

![](/resources/img/blog/survey/2.png)

<em class="center small">Type profile distribution, grouped by team size</em>

We'd need to look at relative results to test whether there's a correlation or not though. So here goes, but keep in mind that the group with `2-10 people`, is by far the largest.

<div class="image-noborder"></div>

![](/resources/img/blog/survey/3.png)

<em class="center small">Relative type profile distribution, grouped by team size</em>

As I expected, based from my discussions: profiles `D` and `E` are more present in smaller teams.

Next I looked at project size. I asked participants to describe the size of an average project they work on: small, medium, large or extra large.

<div class="image-noborder"></div>

![](/resources/img/blog/survey/4.png)

<em class="center small">Relative type profile distribution, grouped by project size</em>

This chart does a growth of type `A` and `B`, related to the size of the project. Most times, "project size" also translates to "project duration", which is why I also asked participants to rate the project duration of such an average project. 

<div class="image-noborder"></div>

![](/resources/img/blog/survey/5.png)

<em class="center small">Relative type profile distribution, grouped by project duration</em>

Again we see a preference for stricter type systems in longer projects, but we should of course be aware that there were less participants in these groups.

## Final conclusions

Unfortunately, I think there weren't enough participants distributed across all kinds of projects and team sizes to draw final conclusions here. 

Up front, I assumed that the group who preferred not to use type systems would have been larger; but maybe it's simply a more vocal group, even though smaller? I can't say that for sure though.

I do think that, even with a small amount of participants, we can assume there is a correlation between type system usage and project- and team size; but I'd need to conduct a larger survey to prove that.

My personal takeaway is that when entering type system discussions, we should be wary to compare each others preference: there might be a good case that you're simply working in a completely different kind of project, and there's no way of telling who's right or wrong.

---
title: Open source strategies
meta:
    canonical: https://tempestphp.com/blog/open-source-strategies
---

This post was originally published on [the Tempest blog](https://tempestphp.com/blog/open-source-strategies).

---


Imagine getting a group of 20 to 50 random people together in a room, all having to work on the same project. They have different backgrounds, educations, timezones, cultures — and your job is to guide them to success. Does that sound challenging enough? Let's say these people come and go whenever they please, sometimes finishing a task, sometimes doing it half, sometimes having AI do it for them without any review, and some people are simply there to angrily shout from the sideline. 

Writing it like that, it's crazy to think that any open source project can be successful. 

However, many projects are, and I've got to experience that first hand, being involved in open source for over a decade. First were some hobby projects, then I worked at [Spatie](https://spatie.be/open-source) where I helped build and maintain around 200 Laravel and PHP packages, and in recent years there's [Tempest](https://github.com/tempestphp/tempest-framework). What's interesting is that, even though I know fairly well how to code, "open source" was a whole new skill I had to learn; one I've come to like as much as writing actual code (or maybe even more). 

At its core, **open source is a "people problem", more than a technical one**; and for me, solving that problem is exactly what makes open source so much fun. 

Over the years, I had to learn several ways of navigating and dealing with that "people problem". Some things I learned from colleagues, some from other open source maintainers, some lessons I had to learn on my own. In this post, I want to bundle these findings for myself to remember and maybe for others to learn.

## Putting my ego aside

In the past, I've definitely worked on open source projects chasing my own fame and fortune. However, looking at [Tempest's contribution stats](https://github.com/tempestphp/tempest-framework/graphs/contributors), I can only conclude that there is no such thing as _my_ open source project. It was only able to get where it is now because of the efforts, contribution, and collaboration of many people — oftentimes more skilled and talented than me.

I realized that by empowering others, the project benefits. This sometimes means putting _my_ needs aside and truly listening to the needs of others. That isn't always an easy thing to do, but it has a very powerful consequence: when contributors feel appreciated and acknowledged, they often want to be involved even more. Eventually they themselves become advocates for the project, leading to even more people getting involved, and the process repeats.

Helping others to thrive is a core principle in successful collaborative open source. 

## BDFL

It might seem contradictory to my first point, but I'm a firm believer of _one person having the final say_ — a [<u>B</u>enevolent <u>D</u>ictator <u>F</u>or <u>L</u>ife](https://en.wikipedia.org/wiki/Benevolent_dictator_for_life). That's what many popular open source projects have called it in the past.

Where people come together, there will inevitably be differences in opinions. Some opinions might be objectively _bad_, but frequently there are _gray_ areas without one objectively _right_ answer. When these situations arise, a successful open source project needs _one person_ to make the final decision. This _dictator_ should, of course, take all arguments into account. Likely they will surround themselves with a close group of confidants, but in the end, it's their decision and theirs alone. They guard the vision of the project, they make sure it stays on track.

## Say no

Sometimes an idea isn't bad at all, but still I have to say "no". 

Because of the "open" nature of open source, people come and go. They contribute to the codebase free of charge, but they are equally not obliged to maintain their code either. In the end, it's me having the final responsibility over this project, and so sometimes I say "no" because I don't feel capable or comfortable maintaining whatever is being proposed in the long run.

## Say thanks

Whether I merge or not; whether a PR is the biggest pile of crap I've ever seen or not; I make a point of always saying thanks. Think about it: people have set apart time to contribute to this project. The least I can do is to write a genuine "thank you" note.

For the same reason, I try to be quick in responding to new issues and PRs — I don't always succeed, but I try. This lets people know their effort is seen — even though it might eventually not end up being merged. I try to value the intent over the result, which again, circles back to making others thrive.

## Opinion driven

I prefer code to be opinionated. Trying to solve all problems and edge cases is a fallacy, especially within open source where there will always be someone coming up with a use case no one else in the world has thought of. The reality is that time and resources are limited, which means that adding all knobs and pulls and configuration to please everyone is impossible.

Years of practice have shown that this strategy works. While people are often taken aback by it at first, it turns out to not be the blocker they feared it would. 

## Automate the boring parts

Besides the people side of open source, my passion is still with code. With Tempest, I'm lucky to have a friend who's very skilled with the devops side and has helped set up a robust CI pipeline. I probably wouldn't have been able to do that myself without help (and many frustrations), but I simply cannot live without it anymore: from code style reviews to static analysis, from testing to subsplitting packages; everything is automated, and it saves so much time.

## Keep moving forward

I tag often — usually whenever there's something to tag — I'm not limited to a fixed release cycle. This means that people's contributions become publicly available very quickly, which contributors seem to appreciate. 

One thing to take into account with having so many new releases (sometimes several per week, sometimes even several per day), is that you have to disconnect "releases" and "marketing" from each other. Where many open source projects think of "a new major release" as a once-every-one-or-two-years event that has to generate lots of buzz, I find that disconnecting the two makes life a lot more easy. I write feature highlight blog posts whenever there's time to do so, and simply mention "this feature is available since version X".

Another positive consequence is that you can easily spread out public communication about your project across time, which tends to have a strong long-term effect than communicating "everything that's new" in a single blog post or video.

## Take breaks

Finally: the realization that the world won't end when people take a break. I just had a three-week break where I totally disconnected. It seriously helped me to reenergize and sharpen my focus again. I want to encourage regular contributors to my projects to do the same. Take a break, you're winning in the long run.

---

For now, those are the things I wanted to write down. If anything, I'll use this list as a personal reminder from time to time to keep my priorities straight. And maybe it'll help others as well. 
---
title: No more issues
---

An [interesting change at Laravel](https://x.com/taylorotwell/status/2095516796748996843) yesterday: they disabled the ability to create issues on a number of repositories and instead encourage people to create pull-requests instead:

![](/img/blog/issues/1.png)

To be clear: this is only the case for Laravel packages like Socialite and Scout, it does not affect the main Laravel repository. Taylor mentioned issues on the framework repository are still enabled, although it's unclear if that will remain or not.

As an open source maintainer myself, I find it a fascinating change, and I've got a couple of thoughts.

## From the perspective of maintainers

I would say it's a change that mainly benefits the maintainers, not necessarily the users. From the maintainer's point of view, I have to admit: I'm also more likely to invest time in an issue if the submitter is willing to submit a PR for it. There simply isn't enough time in a day to do everything, and oftentimes you have to prioritize. Forcing contributors to immediately submit a PR does potentially reduce the triaging time.

That being said, in this era of AI, submitting a PR has become almost as trivial as submitting an issue. On top of that, my experience tells me that AI-generated PRs require a lot more review effort compared to when people have thought possible solutions themselves before submitting a PR (and requiring them to create issue for it is a good way to encourage critical thinking).

We'll circle back to AI in a second though, first, some other considerations.

## PR spam

Let's say Laravel accidentally releases a bug that affects a large number of people. Chances are likely you'll get dozens of PRs fixing that same issue, hours after that release. Now, you could argue that this is the same amount of overhead compared to issues, because if issues were still allowed, chances are there would be dozens of issues for the same bug that also have to be closed.

The main problem I see with "PR spam" isn't necessarily the number of PRs, but rather the amount of work they trigger: automated CI actions have the potential to run many more times because duplicated PRs. That's a problem you don't have with issues. Of course, GitHub Actions can be configured to not trigger for every PR, so maybe this is a non-issue (🥁) after all.

## We're in this together

One good thing I think this change highlights, is that we're in this together. Open source isn't about just dumping an issue and expect someone else to fix it. You're encouraged to contribute, and without contributions, most open source projects would fail.

That being said. Laravel isn't really an open source company anymore. They are selling products and services to an ecosystem that originated from open source. From a business point of view, with a team working full-time on open source, the change seems odd.

## Lowering the bar

I always say that for every issue created, there likely are hundreds if not thousands of people who encounter the same thing and just don't bother reporting it. Think about it: someone actually took the time to stop what they were doing, head over to GitHub to report and describe a problem with code _I_ am responsible for. 

That, in itself, is quite the contribution. It's why I always make it a point to properly acknowledge and thank issue reporters, even if their issue is wrong, or I don't intend to fix it. They have already dedicated some of their precious time, which can only be appreciated.

So my opinion is that an open source project should do anything to lower the bar of contributing, not make it higher. Requiring people to submit PRs instead does raise the bar.

## Inclusivity

Finally, there's the point of inclusivity (related to AI, that is). Now, I don't think Taylor meant to say you MUST use AI to submit pull requests. I don't think any open source maintainer would care if you _didn't_ use AI. However, it is indeed true that using AI in a codebase you're unfamilliar with might allow more people to contribute.

Although, that argument also goes the other way: there are still many people not using AI, some limited by financial means, others rejecting AI on ethical concerns; and indeed: the investment in sending a PR without AI might be too big to people who'd have no problem submitting an issue.

There's also the matter of programmer exprience. Because in the end, LLM-generated code still needs an experienced human eye to review. [Taylor even says that himself](https://github.com/laravel/vapor-cli/pull/285):

![](/img/blog/issues/2.png)

With or without AI, this change does exclude less experienced programmers from contributing (even if it's only through issues). Many of us started our programmer journey with Laravel, and many of us started out as total noobs. What about these people in the long term?

## So now what?

I don't want to draw any hard conclusions from this change. I simply listed some of my thoughts, and am curious to hear others. I can see why open source maintainers would choose Taylor's approach, although I also have some questions and reservations. It's definitely a filtering method that could help reduce the load for maintainers, but I also find it hard to reconcile with my belief that every contribution is valuable and poses a learning opportunity for newcomers — even if it's _just_ an issue. Maybe that's not the case anymore at the scale of Laravel? 

Let me know your thoughts! You can leave them in a [comment](#comments) or wherever you're reading this. 

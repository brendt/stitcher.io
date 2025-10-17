---
title: 'What a good PR looks like'
meta:
    description: "It's Hacktoberfest, be nice and don't spam"
---

It's [Hacktoberfest](*https://hacktoberfest.digitalocean.com/), your chance to contribute to open source and win a t-shirt while at it! Being an open source maintainer [myself](*https://github.com/brendt), I find it a great initiative, but it comes with responsibility as well. Many people try to be smart and send low-effort, borderline-spam PRs, essentially [ruining](*https://joel.net/how-one-guy-ruined-hacktoberfest2020-drama) a maintainer's day.

So to help maintainers _and_ contributors in creating quality open source, here are a few tips!

## Start by looking at existing issues and PRs

Even though most PRs are well meant, it's important to first look around the repo for existing issues or PRs addressing the same problem. Someone else might already be working on a similar PR or there might be issues preventing the PR from being made. If you want to help out, start by participating in the discussions already happening, instead of working on your own.

## Discuss first

If nothing has been said about your feature, it might be a good idea to discuss it first, instead of going ahead and potentially changing hundreds of lines of code. I'll be the one who has to maintain your PR for the foreseeable future, so it's best to first discuss it with me! I can probably give you some tips about the code base, as well as tell you about our expectations regarding implementation. 

You can choose to open an issue first, or submit a draft PR with a minimal implementation, a proof of concept. I actually really like those draft PRs: it visualises what you want to change, but also indicates that you realise there's more work to do. 

{{ cta:mail }}

## Follow the style guide

Speaking of expectations, check whether the repository you're committing to has any code style guide or linter set up. **Follow those rules**. Don't submit a PR that deliberately uses other styling rules. You're submitting code to another codebase, and that style guide has to be followed.

A note to maintainers: you can help out here by using GitHub actions to automatically run your linters on PRs.

{{ ad:carbon }}

## Document your PR

I'm going to review your code, so I'd like it to be as clear as possible. This starts by writing [clean code](/blog/a-programmers-cognitive-load): using proper variable names, maybe even add a doc block here and there; but it's equally important to explain your thought process as well. You could use review comments to clarify specific parts of the code, or you could add some general comments in the PR. 

## Clean commits

Please try to avoid `"wip"` commit messages, please? You don't have to write a book for every commit, and I realise that you might not want to think about them while in the zone; but something like `"Refactor XX to YY"` is already infinitely better than `"wip"`. If you really want a good commit message, try to have your commits only do one thing, and try to explain _why_ this commit is necessary. 

{{ cta:dynamic }}

## Only relevant changes

Only submit changes that are relevant within the scope of the PR. You might be tempted to fix another thing or two while at it — and you're allowed to — but keep those changes in a separate PR.

You can always base a new branch on your PR branch if necessary, and mention in the second PR that it depends on the first one to be merged. I'm happy to merge them for you, in the right order.

## Be patient

It might take a while for maintainers to merge your PR. I even confess to have lost track of a few PRs over the past years. A friendly "bump" after a few days is always appreciated, but it still might take some time. Remember that most OSS maintainers are doing this on a voluntary basis, so don't be mad when a PR takes a little longer to merge.

## Be friendly

This goes for both maintainers and contributors: don't be a jerk. Sometimes a PR gets declined, sometimes a maintainers looses their patience. Stop and breath, realise it's not the end of the world and move on. Be friendly and respectful.

## Don't wait too long to tag

This one is for the maintainers: one of the most frustrating things is a PR getting accepted, and than having to wait another month for a release to be tagged. You shouldn't be afraid of high version numbers, that's what semver is for. Tag the release as soon as possible, and please don't wait another week!

---

Do you have any more tips? Let them know via [Twitter](*https://twitter.com/brendt_gd) or [email](mailto:brendt@stitcher.io). Here's already another good read by my Colleague Sebastian, on how to write [a good issue](*https://sebastiandedeyne.com/a-good-issue/).

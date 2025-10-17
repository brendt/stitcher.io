---
title: 'What is your motivator?'
disableAds: true
---

Over five years ago, I began a new project. I called it "Aggregate", and it was meant to be a community-driven RSS feed. People could suggest content from all over the internet, and it would all end up in one big aggregation feed. I kept working on it over the years, and it eventually became my primary source of staying up to date with what was happening around me in the community.

For the last year, though, I struggled with it. I really wanted to make a couple of UX improvements that would help me moderate the content, but I wasn't too excited to dive back into the code. When I started the project five years ago, I chose Laravel. Over the years, I had struggled to keep up with the new major releases, though, and ever-changing frontend stacks scared me even more. Given that I mainly wanted to make frontend UX improvements, it eventually led to not wanting to moderate the feed anymore. That meant no more content was published, and the project was slowly dying out. I still believed in the core concept, and I knew it was actually really helpful to me, but the level of code rot felt too intimidating to me.

All of that changed a week or two ago when I reviewed my old [impact charts](https://stitcher.io/blog/impact-charts): Aggregate always had a positive impact overall, and I felt sad about abandoning it. At the same time, I had no motivation to dive back into an outdated Laravel project. So I figured: there now is another project I've been working on that might help me. Sure, [Tempest](https://tempestphp.com/) is far from "feature complete", but by now it was more than capable of handling a project the size of Aggregate. The Tempest 2.0 release felt like a good excuse to "build something new".

And so, I found a new motivator: to try out Tempest 2.0 (likely discovering a bug or two) and to rebuild Aggregate from scratch. I took the opportunity to remove a bunch of niche features that got added to the project over time but never proved valuable. I also rewrote the entire frontend and added the necessary UX changes: I included HTMX and simplified the moderation workflow significantly.

And the best thing? Because of the simplified codebase and the UX improvements, I found the drive to moderate again. I'm working through the backlog of around 2000 blogposts that were added over the year that I abandoned the project, and I'm discovering a lot of interesting content along the way.

I wanted to write this post because of two reasons. First to let you know that Aggregate is active again! You can take a look on [aggregate.stitcher.io](https://aggregate.stitcher.io/), you can follow via RSS, and of course make your own suggestions on what should be added to the list!

Second, I found it interesting how a simple motivator like refactoring the codebase had such a huge impact on how I enjoy the project as a whole. It made me wonder: do you know your motivators? I'd love to [hear from you](mailto:brendt@stitcher.io)!
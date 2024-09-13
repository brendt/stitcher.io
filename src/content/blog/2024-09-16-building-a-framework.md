Ten years ago, I flew with my girlfriend-now-wife to Billund in Denmark to meet her sister who recently moved there. I had just finished school and was about to start my first job a couple of weeks later as a WordPress developer, so I had some time off, and we decided a trip to Denmark would be nice.

This is us on the airport — albeit with terrible photo quality and equally terrible hair on my part:

![](/resources/img/blog/airport/airport.jpg)

We had a great time but after a couple of days, we had to return home. My wife's sister's husband (my future-brother-in-law, I hope it's not too confusing 😅) drove us back to the airport. We had some time to kill before checkin, and so he stayed with us until we had to leave. We talked about that upcoming job of mine, which I told him probably wasn't my dream job, but it was a start. He asked: "if you could choose, what would you really like to do?" I didn't have to think about it all that much: "write a framework," I said. He's in IT as well, so he knew exactly what that was about. 

When I properly learned programming in college, I had always found frameworks to be the most interesting problem space: it's on a lower and more abstract level than project code, but you still do very practical and tangible things with it. I had written something that looked vaguely familiar to CodeIgniter as my dissertation a couple of months earlier, so I had an idea of what such a project entailed.

Still, dreams are just dreams, I was heading into the real world now.

I started my WordPress job a couple of weeks later, quit two weeks thereafter (it really sucked 🤣), then joined a startup, which sucked as well, then I ended up with a webdev agency that was really nice. I worked there for around four years. Coincidentally, they had written their own framework and had built a hundred or two hundred websites with it. My colleagues were great, but the framework really sucked. It kind of destroyed my faith in being able to ever write something on my own. I even came at a point where [I swore I'd never chase that dream again](/blog/dont-write-your-own-framework). 

Evidently, things have changed a little bit…

I joined Spatie in 2017. They had this great open source reputation and dared to build things from scratch, but this time [it worked](https://spatie.be/open-source). Slowly but surely I regained confidence that my dream of building something on my own shouldn't stay a dream. But on the other hand, there was Laravel now, so PHP didn't need another MVC framework. So I did some fun open source projects with Spatie and wrote some books. I got to create things for the community, and that felt really nice.

Then I got into contact with JetBrains, where I could be a developer advocate for PHP, meaning I could essentially turn what used to be my hobby of blogging, vlogging and open sourcing into my full time job. I loved Spatie, but this was an opportunity I couldn't let slide. 

Part of my job involves [PHP Annotated](https://www.youtube.com/@phpannotated), the YouTube channel. A year ago I started experimenting with livestreams instead of videos. My goal was to sit down with fellow developers and just talk and connect with people. I figured the thing all of us had in common was code, so I picked up some framework code I had written a year earlier as some kind of "code kata" (it was basically a router and dependency container), and I started building a framework from scratch, on stream. I was very clear though that this was just meant to be a learning experience, nothing serious. 

However, people picked up on some of the ideas I pitched for this "framework". Stuff like my no-config approach, modern-syntax first (like attributes), discovery (which Aidan pitched), and plug-ability into any project, including other frameworks — people seemed to like these ideas. These were things that Symfony and Laravel don't do, or not to the same extent; and people seemed to really like it. They started to contribute. Sure, there aren't hundreds of contributors like with Laravel, but still over 20 people contributed up until this point. I kept streaming, and people kept watching and talking. We started a Discord server which now has over hundred members.

And suddenly… I ended up with something more than "just a thought experiment". There seem to be people that want to _use_ this framework. Not for anything big, I reckon, but for like, hacking together a small blog or website. I'm personally refactoring Aggregate from Laravel to it. It's pretty fun.

So, it's a weird situation we're in now. Apparently I'm realising my 10-year-old dream without ever having intended to do so. Well let's not get ahead of myself: this might all still be a fiasco in the end. That's ok as well, since I never even intended for it to be something. However, I am at a point where I need some clarity: what is this thing we're working on exactly?

So, a couple of weeks ago I decided to make a proper roadmap: what is needed for this framework to be usable within a very limited context? To be usable without people rage-quitting after 1 minute? I made a list, and I worked on that list together with several other developers. Now we're at a point where I _think_ the framework is alpha-material. Alpha meaning: there will be bugs, lots of bugs. But it's also a phase where I can test the waters: are people _really_ interested in playing with it, or not? Will people want to help out by submitting bug reports, or maybe even submitting PRs, or not?

For me, both outcomes are fine, but I do want to know which way we're heading. 

So: next week I'm tagging the first alpha release of Tempest — that's the name, by the way, I really like it. My hope — no it's not "a hope", I just want to figure out if people are interested Tempest or not, and at what scale? And, like I said: both outcomes are fine.

So, there's that. If you want to check it out, the links are:

- [https://tempest.stitcher.io](https://tempest.stitcher.io/) for the docs;
- or [https://github.com/tempestphp/tempest-framework](https://github.com/tempestphp/tempest-framework) for the code.

Let me know your thoughts! The best way is to [join the Tempest Discord](https://discord.gg/pPhpTGUMPQ), or to [send me an email](mailto:brendt@stitcher.io).

That's it, thanks for reading!
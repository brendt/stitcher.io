_This post was originally sent to [my newsletter](/newsletter/subscribe)._

I've written a lot about PHP 8.1 these past months. Not just about the release itself, but also about how important it is to keep up-to-date with PHP versions.

I don't want to leave you with empty words though, so I want to share some actionable advice on how to deal with updating external dependencies. Because, let's face it, you  _want_ to update your project to PHP 8.1, but some of your dependencies don't support it. So, end of story, right?

Here's what I do in such cases.

First off, I **don't wait** for the official PHP release to start testing my code. This goes for client and private projects, as well as open source projects. You can safely start testing once PHP's release candidates arrive. You might even discover a bug or two in PHP's core, helping them along the way.

Next, if you encounter an external dependency that doesn't support the latest PHP version yet, try and **send a pull request** to fix it yourself. Open source maintainers will often be very thankful for your contribution. It might be best to check the open issues, pull requests and discussions though, maybe it's already being worked on.

Of course, it's not always possible to send a PR on your own. Either you don't know the codebase well enough or don't have the time. The next step is to **reach out to the maintainers**, politely ask if they can provide PHP X.X support and whether you can help in any way. Even when you're not able to actually _code_ the required changes, you might be able to test those changes in your project, and provide early feedback. 

The benefit of starting early, is that you're not as much pressured for time. Maybe it'll take maintainers a bit longer than you anticipated, so starting early is only beneficial.

Let's fast-forward in time. PHP 8.1 is released, and one of your dependencies still hasn't been updated. Either the maintainer seems to be inactive, or there's a roadblock that can't be solved in a reasonable amount of time. **Start looking for alternatives**. This is the reason why you shouldn't pull in any dependency into your project without doing your research first. You should look into who and how many people are maintaining it, whether their work is funded, whether there's recent activity in the repository or not, and how many open and stale issues there are. It's best to do this kind of research before, instead of having to deal with it right when want to upgrade your project. 

It's impossible to predict the future though, so you'll have to deal with inactive projects one day or another.

If you can't find any alternative dependencies, or none that can be implemented in a reasonable amount of time; you can consider **forking the package** and pulling it into your project. This should always be a last-resort option, but it might be worth it. Realise that you're adding tons of technical debt to your project by doing so though, so carefully consider the pros and cons. Also avoid trying to maintain a public fork, unless you're really motivated to do so.

Now, this strategy seems to work fine in client projects; but what about open source projects that have dependencies themselves? Things get a little more difficult when your open source code turns out to be dependant on other code, code that doesn't support the latest PHP version. Our last resort — forking — isn't as trivial when hundreds or thousands of projects depend on that code as well.

If you're an open source maintainer, I'd say the rule about picking your dependencies is even more important for your open source code. Forking "in the open" comes with a lot of headaches that you want to avoid at all costs. 

While it's still a valid strategy in _some_ cases, it might be worth to look at it from a different angle.

Being active in the Laravel community, I actually think we're rather fortunate. There are companies that pay employees to work on open source code. Laravel itself is the best example. I spoke with [Dries](https://twitter.com/driesvints) in preparation for this post, one of the maintainers of Laravel. I asked him about the most difficult things when it comes to dealing with external dependencies from Laravel's point of view. He said it's mostly a waiting game. On top of that there's lots of communication between them and external package maintainers to get everything working.

So if you're in open source, the best thing you can do is to carefully consider what packages you depend on. Keep a good relation with those maintainers and try to help where you can. If, on the other hand, you're an open source user; you or your company should consider supporting the people you depend on. They are doing incredibly important work; most of the time, for free.

{{ cta:mail }}

That's all I've got for you today. Though I'm wondering: are you running up-to-date PHP versions? If not, what's keeping you back? Are external dependencies the problem, or maybe you're dealing with server constraints? Maybe it has to do with time or budget issues or something else? Let me know via [email](mailto:brendt@stitcher.io) or [Twitter](https://twitter.com/brendt_gd), I'm curious!

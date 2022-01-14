I've written a lot about PHP 8.1 these past months. Not just about the release itself, but also about how important it is to keep up-to-date with PHP versions.

I don't want to leave you with empty words though, so I want to share some actionable advice on how to deal with updating external dependencies. Because, let's face it, you  _want_ to update your project to PHP 8.1, but some of your dependencies don't support it; so, end of story, right?

Here's what I do in such cases.

First off, I **don't wait** until the official PHP release to test my code. This goes for projects as wel as open source projects. You can safely start testing once PHP's release candidates arrive. You might even discover a bug or two in PHP's core, helping them as well.

Next, if you encounter an external dependency that doesn't support the latest PHP version yet, try and **send a pull request** to fix it yourself. Open source maintainers will often be very thankful for your contribution. It might be best to check the open issues, pull requests and discussions though, maybe it's already being worked on.

Of course, it's not always possible to send a PR on your own. Either you don't know the codebase well enough or don't have the time. The next step is to **reach out to the maintainers**, politely ask if they can provide PHP X.X support and whether you can help in any way. Even when you're not able to actually _code_ the required changes, you might be able to test those changes in your project, and provide early feedback. 

The benefit of starting early, is that you're not as much pressured for time. Maybe it'll take maintainers a bit longer than you'd want to add support, so starting early is only beneficial.

Let's fast-forward in time. PHP 8.1 is released, and one of your dependencies still hasn't updated. Either the maintainer seems to be inactive, or there's a roadblock that can't be solved in a reasonable amount of time. **Start looking for alternatives**. This is one of the reasons you shouldn't pull in any dependency into your project without first looking into who and how many people are maintaining it, whether their work is funded, whether there's recent activity in the repository or not, how many open and stale issues there are. It's best to do this kind of research before, instead of having to deal with it when you actually just want to upgrade your project. It's impossible to predict the future though, so you'll have to deal with inactive projects one day or another.

If you can't find any alternative dependencies that can be implemented in a reasonable amount of time (how long depends on your project, deadlines, team and budget), you can consider **forking the package** and pulling it into your project. This should always be a last-resort option, but it might be worth it. Realise you're adding tons of technical debt to your project by doing so though, so carefully consider the pros and cons. Also avoid trying to maintain a public fork, unless you're really motivated to do so.

Now, this strategy seems to work fine in client projects, but what about open source projects who use external dependencies themselves? Things get a little more difficult when your open source package depends on other open source packages that don't support the latest PHP version. The last resort, forking, isn't as trivial when hundreds or thousands of projects depend on that code as well.

<div class="quote">

## What's new?

It's been a productive week, coming back from holiday I really felt energised and inspired to write some posts. Here's what I've worked on this week:

- [How I plan](https://stitcher.io/blog/how-i-plan)
- [PHP version stats: January 2022](https://stitcher.io/blog/php-version-stats-january-2022)
- [PHP in 2022](https://stitcher.io/blog/php-in-2022)

Also, I made a new video! It's focussed on people who haven't heard of PHP or know little about it, to give them a quick rundown of the language. If you're a PHP developer, you probably won't learn anything new, although it might be helpful to teach some people you know that PHP isn't the same crappy language it used to be 15 years ago.

<a href="https://www.youtube.com/watch?v=IfcFQxYPTxo" target="_blank" rel="noopener noreferrer">
    <p>
        <img src="/resources/img/static/php-in-7-minutes-thumb.jpg" alt="PHP in 7 Minutes">
    </p>
    <p class="center"><em class="small">PHP in 7 Minutes</em></p>
</a>

<br>
As always, a like and comment is very much appreciated!

</div>

That's all I've got for you today. Though I'm wondering: are you running up-to-date PHP versions? If not, what's keeping you back? Are it external dependencies, or server constraints? Maybe it has to do with time or budget issues or something else? Let me know, I'm curious!

Until next time!

Brent

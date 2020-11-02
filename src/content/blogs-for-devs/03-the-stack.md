In this chapter we'll discuss the best technical stack for your blog. Disclaimer: there's no perfect one, and the simple approach is often the best.

---

I want to make something clear: you're building a blog, not Facebook or Reddit. The only thing your website needs to do is serve a few simple HTML pages, CSS scripts and maybe an image here and there. I want to take you back to May, 2019; more specifically Wednesday the 15th.

This was the first time one of my posts went viral on Hacker News. I posted a link without thinking too much of it, and noticed two hours later that more than 300 people were browsing my blog at once. It turned out the post was rather well received, and eventually ended up with [908 upvotes](*https://news.ycombinator.com/item?id=19917655).

This is what going viral on Hackernews looks like:

![Pageviews per hour on May 15th and 16th, 2019](/resources/img/blogs-for-devs/03-01.png)

Let me tell you, this was one of the biggest adrenaline rushes I've felt in my entire life. That can either mean I have a very boring life, or that it's very cool to go viral — I'm happy to leave that question unanswered.

My blog was serving around 1000 requests per minute (including images and resources); and it never went down, performance was as good as ever. Did I have a fancy serverless setup that could horizontally scale? Did I have a complex caching layer or a third party CDN like Cloudflare?

Nope, just a static website running on the cheapest digital ocean droplet.

Now, my blog is powered by my own hobby-project static site generator written in PHP; but when you're reading this, it's all just HTML pages saved on the server. It turns out the simple solution is often the best one. Like I said: you're not Facebook or Reddit, you'll be fine with the cheapest solution you can think of. 

In fact, I'm still curious to know the limits of my current setup, because I haven't encountered them yet.

{{ cta:blogs_mail }}

Stick with the simplest stack possible. I'd advice to use a static site generator, or cache your blog statically if you're using a blogging engine like WordPress. If you're a developer like me, you _could_ probably build something on your own, but honestly I'd advice against that.

I must admit that I probably wouldn't build my own static site generator again if I had to start from scratch today. There are enough open source and free alternatives out there. I still like my own solution because I can hack it whatever way I want and add little niceties, but honestly it's easier to start with something you can set up in 30 minutes and forget about it afterwards. Remember: [Content is King](*/blogs-for-devs/02-content-is-king), so don't waste too much time on setting up a complex solution. You can worry about that later if you really want to.

<div class="sidenote">
<h2>In summary</h2>

- You're not Facebook, you'll manage fine with the most basic setup.
- Focus on writing, don't spend days upon days with the technical side.
- Simplicity is key when starting, you can always expand later.
</div>

{{ cta:blogs_more }}

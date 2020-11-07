While the social media driven hype-spikes often result in an adrenaline rush, they don't grow your blog as significantly as you might think. Even when you offer them ways to [stay in touch](*/blogs-for-devs/06-stay-in-touch), only a small portion of them will ever return. I admit: most of the times I read a blog post myself, I'm not aware of which blog I'm reading it on.

Take a look at the amount of returning visitors, grouped by the channel they used to visit my blog:

![Returning visitors per channel per week](/resources/img/blogs-for-devs/07-01.png)

It's clear that there's one channel above all that's the best way to ensure people return to your blog: Google. So how to ensure Google knows where you are, and to have your content show up in search results? 

It's time for some SEO.

{{ cta:blogs_mail }}

I admit I kind of dislike the term "SEO", it makes me think of dark marketing patterns. SEO doesn't always have to have a dark side though: you don't need to do thorough keyword analysis, write content specifically for Google or buy backlinks to your blog. 

As long as you keep writing [great content](*/blogs-for-devs/02-content-is-king) and invest in [genuine online relations](*/blogs-for-devs/05-interaction), Google will find you.

There are five practical tips I want to give you that don't rely on all the dirty things SEO is associated with. Let's begin

## Content and Performance

We've covered both topics before, but I need to briefly mention them again: your primary focus should always be to write great content. Never write just to boost your SEO performance. That kind of content usually turns out to be crap, and often doesn't even make that much of a difference. So forget about SEO-focussed posts, there's better ways to spend your time.

What Google _does_ take into account is site performance and mobile experience. I already told you that you [don't need a complex setup](*/blogs-for-devs/03-the-stack) to have a performant blog, just make sure to spend some time on optimise it properly.

## Internal links

I've mentioned this before: I think of my content as more than standalone posts. They are a network of connected thoughts and ideas, and it's worth linking them together. With that I literally mean having hyperlinks to your own content. It will help Google better understand the content of your site. If one post is doing well in Google search, and it has connection to other posts, Google will be able to connect the dots. Once I started investing in internal links, I could see search traffic rise steadily.

Also make sure that Google knows how to crawl your site. Add a [robots.txt](*https://support.google.com/webmasters/answer/6062608?hl=en) and build a [sitemap](*https://support.google.com/webmasters/answer/183668?hl=en). We'll discuss how to analyse your technical setup in a later chapter.

## Cross posting

With cross posting I mean actively posting your content on other platforms yourself. I'm an advocate of taking control of your content by hosting it on your own blog, but you can still use blogging platforms like Medium or Dev.to to your advantage. 

The reality is that you'll reach a wider audience if you cross post, but there's one very important thing to keep in mind: you _always_ need to make sure to mention the original post in the form of a canonical URL. Google usually punishes duplicated content, unless you specifically tell them you know it's a duplicate, and that it's ok. 

Adding a canonical URL is as simple as adding the following HTML tag in the `head` of your page. Here's what it looks like for this chapter:

```
<<hljs keyword>head</hljs>>
<!-- … -->

<<hljs keyword>link</hljs> <hljs prop>rel</hljs>="canonical" <hljs prop>href</hljs>="https://stitcher.io/blog/07-be-searchable">
</<hljs keyword>head</hljs>>
```

You need to add this canonical URL to both the original post, as well as all cross posts. Luckily platforms like Medium, Dev.to and Hashnode all provide the option to set it up automatically. 

Cross posting not only allows you to reach a larger audience, but also shows Google your post is more present on the world wide web, and will boost your rank in search results.

{{ cta:blogs_mail }}

## External sharing

People will already share your content on social media, which also has an impact on SEO; but even better is it when other popular blogs or platforms start sharing your content. There's little you can do to force people though (I wouldn't invest in buying backlinks), but writing quality content and being an active person on social media can help.

## Long-tail keywords

I you can't help it and want to invest in keywords to help improve your content, it's my experience that it's best to invest in long-tail keywords. These are keywords with a relatively small amount of searches, which means your content will more easily end up higher in the search results.

You could use a free Chrome extension called [Keyword Surfer](*https://chrome.google.com/webstore/detail/keyword-surfer/bafijghppfhdpldihckdcadbcobikaca?hl=en) to analyse what keywords are best to invest in.

Let's look at an example.

Say you want to write something about "PHP"; search for it with Keyword Surfer enabled. The extension will tell you related keywords, and also show their search volume. The higher the volume, the more interest, but also the more difficult to write a post that's higher up. 

Instead of focussing on high performing keywords, try to focus on the lowest ones. For example, Keyword Surfer tells me there's related query `how to install php` that has very low competition. You can click it, and further analyse related keywords.

Those smaller keywords are easy to optimise for, and can be the stepping stone to compete on other, more popular ones. 

Don't focus too much on keywords though. If you write content only with them in mind, you'll end up with an average post at best. You can analyse keywords _after_ you've written a post, and try to add a few related ones then. I'd say that's the better approach to make sure your content stays qualitative. 

---

I'd encourage you to try all these tips out yourself, find a process that works for you. There's one important thing to remember though: be patient. It takes months, maybe even years, before Google starts picking up your posts; don't let that discourage you. 

<div class="sidenote">
<h2>In summary</h2>

- SEO traffic is much more stable than the traffic drive by social media.
- Content and performance are still key.
- Connect your content: Google picks up on this.
- Cross post, but make sure to use canonical URLs.
- External links are valuable, the best way to have people share your content is by investing in relations.
- Long-tail keywords are an efficient strategy, but don't focus too much on them.
</div>

---

{{ cta:blogs_more }}

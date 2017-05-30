Today we're looking into web performance. I'll share some useful links to articles and tutorials written by people with a lot of professional experience on the topic. I am writing from the perspective of a developer who brought pieces of this knowledge into practice. I've learned some lessons along the way, which maybe you can learn from too.

If you want to reach out, whether to talk about performance, or with additions to this post, you can always reach me [via email](mailto:brendt@stitcher.io).

Without further ado, let's dive into the mystial subject of web performance. We'll start discussing the mindset you should have when building performant websites. Then we'll move on to a lot of practical examples, and links to other learning resources.

### Performance mindset

If there's one thing you should take away from reading this post, it's the mindset every web developer should have. I've seen the industry build tools, frameworks and systems to make the life of the developer easier, whilst forgetting what it's really all about. We're not making artisanal pieces of art anymore. (Maybe we never did?) We're generally aiming for fast development and quick results. We're forgetting about what matters in the end: the website and its visitors.

This post is meant for people with that mindset; people who want to become the best developer they could possibly be. Always pushing yourself to the next level for a better end result. If you're a web developer who can relate to this, understanding performance is one of the most important pillars to build upon.

That's it for the philosophical part of this post. Of course I'm completely ignoring the business side of the IT world. I'm not talking about money, time or scope here. I'm talking improving your own development skills, so that you could also use that knowledge and experience in spare time projects or for real clients and work. 

### Web basics: HTML

One of the key components to understand and improve web performance, is to know how the browser renders HTML. There's a lot more to it than you might think, and understanding these steps makes you reason completely different about your own code. Google has the best crash course on the topic: [https://developers.google.com/web/fundamentals/performance/](https://developers.google.com/web/fundamentals/performance/), especially the "critical rendering path" opened my eyes.

Another important concept to understand is static HTML pages. In the end, they are what's served to the user. There's absolutely no need to generate pages on the fly, while the user is waiting to see the result. Dynamic websites abuse the user's time for the sake of easy development. Now I'm not saying dynamic websites are bad. What I do say is that every dynamic website should have the technology in place to exclude the dynamic building phase from the request/response cycle. More on that topic later. If you're into real static websites, [https://staticgen.com](https://www.staticgen.com/) is a good place to find the right tool for your needs.

Moving on to responsive images. Possibly the number one optimisation when it comes to bandwith usage. The responsive images spec was designed to address the issue of large images, or render blocking JavaScript workarounds. It's completely backwards compatible (I'm talking to you Edge), and has a good chance of drastically improving your website's loading time: [https://responsiveimages.org](https://responsiveimages.org/).

### Backend development

I've already mentioned dynamic websites in the previous section. They are of course a must in the modern web; but you should think about which pages really need to render things on the fly, and which could be cacheable. There are many layers of caching possible on the server side. We'll discuss eg. Varnish cache later in this post. Caching your backend code will highly depend on the kind of language and framwork you're using. The most important thing to mention about caching is that you shouldn't view your cache as a layer "on top" of your application. It should be an integral part of all the code you write.

As a PHP developer, I'm used to the strict request/response lifecycle every PHP web application goes through. There are also a lot of other languages which provide the same logic for web applications. This approach is very easy to reason about, but it means the application has to be bootstrapped from scratch for every single request. Libraries like [ReactPHP](http://reactphp.org/) or [AMP](https://github.com/amphp/amp) address this issue by enabling the developer to handle multiple requests from a single bootstrapped application. Asynchronous and parallel applications add a lot of complexity at first, and might be very difficult to wrap your head around. But it might very well mean a huge decrease in response time.

### Server side

Returning to the topic of caching, there's a lot that can be done server side. First of all there are caching headers which you should definitely implement: [https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control).

Second, you should serve content that's ready to be served. Use a CDN and Varnish in front of your real server. This way you're able to serve images, content pages, etc. immediately, while they were generated before. One of the dangers of using a so called "proxy" like Varnish is that many developers might see it as that "layer on top of your own application". In reality, you'll need to communicate a lot with Varnish from within your own application. You can read more about Varnish here: [https://varnish-cache.org](https://varnish-cache.org/).

The benefit of your own server? It's **your server**. You have control over the resources used and available. Don't put extra load on the client, when you could let your server take care of it. This is of course a very simplified way of thinking about resources, but it's always possible to upgrade your server's hardware, when you have no control over the hardware clients are using.

And lastely, if you haven't implemented HTTP/2 yet: implement HTTP/2! Not sure why? This might give you an idea: [https://sitepoint.com/what-is-http2](https://www.sitepoint.com/what-is-http2/).

### Frontend development

**Disclaimer:** I'm a backend web developer. I have written, and still write lots of CSS and JavaScript code, but I'm not in any way a professional when it comes to frontend web development. So I'll only use common sense and reasoning to share a few concepts of performance improvement; and will provide links to other resources, written by professional frontend developers.

You should think what resources a page really needs. If that particular page only needs 5 kilobytes out of 100 kilobytes of CSS, then don't load the other 95 kilobytes! The same goes for JavaScript. 

Also think about inlining the important resources in your HTML pages, at least while HTTP/2 server push hasn't gone mainstream yet.

A good place to go from here would be Tim Kadlec's blog: [https://timkadlec.com](https://timkadlec.com/).

---

### In summary

- Think performance-first.
- Understand how HTML is loaded and rendered.
- Serve content that's ready to be served.
- Don't abuse the user's time by dynamically rendering on the fly when it's not needed.
- Improve the request/response cycle server-side.
- Put the load on your server, not the client.
- Don't view caching as a layer on top, but rather as an integrated part of your application.
- Set browser caching headers, use CDNs and take a look at Varnish.
- Don't load all minified CSS or JS when you only need 10% of it on that page.

Lot's of things to think about. This is my personal checklist I try to keep in mind when developing websites, both professionaly and in my spare time. Like I said at the beginning of this post, you shouldn't always do everything just because. But you should understand these concepts, and know when it's appropriate to use them. By doing so, you're contributing to the better web.

If you have any questions or remarks, feel free to [send me an email](mailto:brendt@stitcher.io).

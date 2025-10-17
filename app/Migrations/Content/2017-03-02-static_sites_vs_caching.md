What's the difference between static site generators and caching you ask? 

A short answer might be: there is no technical benefit between both. But the mindset behind the two is completely different.

I think the topic is too interesting to leave it like that. Let's talk about caching.

For many years now, we've been creating systems which help us build websites. Many of those systems are built around the idea of "dynamic websites". Instead of writing HTML and CSS pages; we've designed systems which can load information from a data source (say for example, a MySQL database); parse that data into so called "templates" (these are like blueprints for HTML pages); and finally send the rendered HTML to the client. Of course, there is more than just HTML and CSS to a website, but that's a topic for another day.

Now imagine you've got many visitors on your website, each of them visiting the same page. Rendering that page for every visit would require more server resources than to render the page once, and send that output to everyone asking it. That's a cached page. You could also cache other parts of the application. For example: not always perform the same database query, but rather cache the result of that query and reuse that over and over again.

Evidently, caching is way more than what I just described. My try at a general definition for caching on the web would be something like this.

> Once a resource intensive operation is done, remember the outcome. The next time the same operation is requested, you can just give the result instead of doing that operation again.

Caching is a very powerful tool which **wraps around** your system, enabling it to be much more performant.

Stitcher, and all static site generators, are the opposite. These tools don't *wrap around* a system. Rather, their core **is** the HTML output. All other things needed by developers to smoothly build websites, are **plugged in** into that core. What's the downside? You'll have to re-render parts of your website before they are visible to the visitor. A tedious task. Luckily computers are good at performing the same tedious tasks over and over again. Re-rendering your website isn't really a bother when you have the right tools available.

Another "downside" of static websites? It requires a bit more thought of the developer. But when could that a bad thing?

So static websites do have their downsides. But take a look at the things you're able to "plug in" that HTML rendering core:

- [Image optimisation](/blog/tackling_responsive_images-part_1): enabling the developer to use the responsive images specification to its full extent, without any work.
- SASS precompiling: I'm not a frontend developer, but these guys tell me that's a must.
- Pagination, overviews and detail pages.
- Parse MarkDown, YAML and JSON into templates and use those templates like in any dynamic system.
- JavaScript and CSS minifying: very important for website performance.
- Things like ordering and filtering data sets.

Some important things are still missing in Stitcher though.

- Form support: although Stitcher will not include form handling at its core. That will be a separate module.
- Frontend filtering of data sets: technically this is possible, but it might have huge performance costs depending on the amount of filters. I will be working on it in the future though.
- Content management: this is also possible, but not from within Stitcher's core. It would be a separate module acting as a client to modify a Stitcher project.

To be clear: I don't think static site generators are the best solution for all websites. But there are lots of cases which could benesfit from using a static site generator over of a dynamic system and caching. I view many caching systems as like putting a bandaid on top of a wound, but not *stitching* the wound (pun intended). Don't forget that clearing caches is one of the most difficult parts of software development. But we should also be realistic: the static website approach mainly targets small to medium websites, not complex web applications.

So if you want to give it a go, be sure to check out a static site generator, there are many!

- [Jekyll](*https://jekyllrb.com/)
- [Hugo](*http://gohugo.io/)
- [Stitcher](*https://github.com/pageon/stitcher-core)
- [Sculpin](*https://sculpin.io/)

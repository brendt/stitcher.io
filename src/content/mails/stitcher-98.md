Hi ::subscriber.first_name::

If you think about it, most programming challenges can be boiled down to one or two things: text processing and data mapping.

Let's see: I'm currently improving [Tempest's ORM](https://github.com/tempestphp/tempest-framework/), which means nothing more than "generating the right queries (text processing), and mapping the data unto objects".

I while ago, I wrote a [code highlighter](https://github.com/tempestphp/highlight). It's basically the definition of text processing. I also built a [console framework](https://tempest.stitcher.io/console/01-getting-started), which is nothing more than processing an incoming command (which is text), and generating the appropriate output (which is text). Routing? Processing an HTTP request (text), map its data to a controller, and eventually return text again. Building a template engine? Text processing.

Especially with programming languages like PHP, within a web context; 99% of things we're doing is processing text and moving data from one point to another. I like that realization, because it means I can boil down what seem to be the hardest problems, into relatively simple pieces. I wanted to share that with you, maybe you have some additional thoughts? You can reply to let me know!

# In other news

I made some more videos about **PHP 8.4 features**, you can check them out in [the playlist](https://www.youtube.com/playlist?list=PL0bgkxUS9EaKNWvKhX_QAiX4vJYLAz7nX). One of the experiments I did was to look at the JIT: it still doesn't seem to make much, if any, difference for real-life web applications. I do wonder about whether it's worth the invested time and complexity, given that you need very niche and specific use cases to benefit from it.

I'm working on **Tempest**! I actually pushed a first, very crude, version of the template engine to main. You can read the docs about it here: [https://tempest.stitcher.io/framework/03-views](https://tempest.stitcher.io/framework/03-views). Definitely let me know what you think about it!

Finally, I published two more chapters of **Timeline Taxi**, you can read them here: [chapter 2](https://aggregate.stitcher.io/post/0f176a2a-9dbd-4105-9a24-1bd480905a9f) and [chapter 3](https://aggregate.stitcher.io/post/4ac70a15-3e41-45e5-97da-78f0e9711f90).

Until next time!

Brent
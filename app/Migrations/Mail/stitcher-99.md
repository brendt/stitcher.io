Hi ::subscriber.first_name::

This is interesting, the asymmetric visibility RFC is gone to voting. If you don't know, the same RFC was already proposed two years ago, but failed. Today there's an updated version. In short, asymmetric visibility allows you to define separate visibility for property read and write operations. You can, for example, make a property publicly readable, but only privately writable:

```php
class Book
{
    public private({:hl-keyword:set:}) {:hl-type:string:} $title;
}
```

I have to admit, I have a couple of use cases for this feature myself, I talked about it in-depth in [this video](https://aggregate.stitcher.io/post/0abf06bf-d894-4b23-aea4-af79d3614e97). I'm also conflicted though, because there is some overlap with readonly properties and it might cause confusion with lots of PHP developers. For example, can you at a glance say what this is about?

```php
class Book
{
    public protected({:hl-keyword:set:}) readonly {:hl-type:string:} $title;
}
```

Truth be told, I only need `{php}private({:hl-keyword:set:})` in my code, so I might never run into the edge cases. But still. Let me know your thoughts, what do you think of this feature?

Right now, internals is pretty divided about it, but the RFC has barely enough votes to be accepted. It's still a week though until voting ends, so it looks like it'll be a close one! I'll keep you posted :)

# Tempest and Lazy Loads

In other news, I finally — FINALLY — managed to finish my preliminary work on Tempest's ORM by adding belongs to and has many support. It took a while, it nearly drove me mad, but I made it. Of course there will be bugs, but for now I'm happy with the results, and taking a break from ORM stuff.

I actually wrote a blog post about how I'm handling lazy relations in this ORM, and I think it's a very intriguing problem to solve. You can [read it here](https://aggregate.stitcher.io/post/54dffa23-9c24-4e64-850e-a761dc25d56e).

# Developer Ecosystem Survey

I also wanted to let you know that JetBrains' Developer Ecosystem Survey is once again live, feel free to take a look: [https://surveys.jetbrains.com/s3/ti-developer-ecosystem-survey-2024](https://surveys.jetbrains.com/s3/ti-developer-ecosystem-survey-2024).

---

In closing, I have a question for you: you might know about Aggregate, my [curated public RSS feed](https://aggregate.stitcher.io/). I'm on the lookout for now blogs to add to it, and wondered if you knew of any must-follow blogs. It could be about PHP, but programming or the web in general fits as well. Let me know!

Brent
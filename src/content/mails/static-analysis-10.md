I want to end this series on a philosophical note. Now that we've seen all the things that static analysers can do in PHP, and talked about the differences between the existing tools; I want to focus on "the why".

Back in 2016, Ondřej Mirtes — the creator of PHPStan — wrote a blog post called [The Three Pillars of Static Analysis in PHP](https://phpstan.org/blog/three-pillars-of-static-analysis-in-php). He started out by saying this:

<div class="quote">

My credo is that everything that can be automated should be automated. Computers are really good at repeating tedious tasks and they don’t usually make mistakes while us squishy humans are defined by making mistakes everywhere we go.
</div>

Yes. I can agree with Ondřej a 100%. Let computers handle the things they are good at, so that we can focus on what really matters — and don't shoot ourselves in the foot while at it.

Ondřej continues to list his three pillars of automation when it comes to static analysis:

- Syntax checking with a linter — will our PHP code actually compile?
- Using tools to automate coding styles
- And finally, using a static analyser to detect errors that would occur at runtime

I find it marvelous to see how much the PHP community has grown over the past decade, and how all these tools have reached a level of maturity. On the other hand, I'm also looking forward to what's still to come, as I believe there's much more room for PHP to grow the next years.

If there's one thing I'd want you to take with you after this series, it's that automating the boring parts is key in writing quality software. Static analysers are such great examples, because of how they gain insights into your code just by reading it — the same way we would do, but only much faster and much more consistent.

I resisted the idea of static analysis for years, I even didn't want to use an IDE at the start of my programming career because it was "too slow". But I think I can safely say that once I started embracing the tools around me I became a better developer.

Embrace these three pillars, you'll be better off in the long run.

---

And so, this concludes the second edition of The Road to PHP. If you missed the first one by any chance, you can check it out [here](https://road-to-php.com/) (it's about PHP 8.1). Special thanks goes to Matt, Ondřej, Nuno and Christoph for reviewing this series!

There's one very important note to make: you'll be automatically unsubscribed from this list (in fact, you already are when you're reading this mail). That means you won't receive any more followups from me. If you want to receive updates about my content though, you can subscribe to my [main newsletter](https://stitcher.io/mail). I'll definitely work on a third edition of the Road to PHP in the future, so if you want to be kept in the loop about that one, my main newsletter is the place to be!

Finally, if I can ask one more thing: I'd highly appreciate it that, if you enjoyed this series, you share it with your friends, colleagues, on social media, etc. It's the only way I can reach more people, and I want to reach as many people as possible when it comes to topics I'm passionate about — just like static analysis. I'm on a mission to get it more popular in the broad PHP community, and for that I need your help.

Thank you very much for following this series, and I'll see you next time!

Brent

PS: don't hesitate to reply to this mail, I'd love to hear your feedback!

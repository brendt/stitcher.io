I want to end this series on a philosophical note. Now that we've seen all the things that static analysers can do in PHP and talked about the differences between the existing tools; I want to focus on "the why".

Back in 2016, Ondřej Mirtes — the creator of PHPStan — wrote a blog post called [The Three Pillars of Static Analysis in PHP](https://phpstan.org/blog/three-pillars-of-static-analysis-in-php). He started out by saying this:

<div class="quote">

My credo is that everything that can be automated should be automated. Computers are really good at repeating tedious tasks and they don’t usually make mistakes while us squishy humans are defined by making mistakes everywhere we go.
</div>

Yes. I can agree with these thoughts a 100%. Let computers handle the things they are good at, so that we can focus on what really matters — and don't shoot ourselves in the foot while at it.

Ondřej continues to list the three pillars of automation when it comes to static analysis:

- Syntax checking with a linter — will our PHP code actually compile?
- Using tools to automate coding styles
- And finally, using a static analyser to detect errors that would occur at runtime

I find it marvelous to see how much the PHP community has grown over the past decade.

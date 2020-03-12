Over the last week, I've been tinkering with a new kind of architecture for PHP applications. I want to tell you up front that I don't think it will solve any real-life problems soon; still I want to involve you in the thought-process. Who knows what kind of great ideas might arise?

In this post I'll go through the architecture step-by-step and address its benefits as well as its downsides — at least, the ones I can think of right now. I do have a proof-of-concept codebase open sourced, and I'll share insights from it throughout this post.

{{ ad:carbon }}

So, first things first, what's this architecture about? It's a long-running PHP server, with its entire state loaded in memory, built from stored events. In other words: it's event sourcing as we know it in PHP, but all aggregates and projections are always loaded in-memory.

Let's break it down!

## A long-running PHP server

The first pillar of this architecture is the long running server. The modern PHP landscape offers several great and battle-tested solutions for managing these kinds of processes.

While day-to-day PHP is often related to a fast request/response cycle, frameworks like ReactPHP, Amphp and Swoole allowed the PHP community to venture into another, unexplored world.

The fast request/response cycle is of course one of the things that made PHP so great: you never had to worry about leaking state or keeping everything in sync: when a request comes in, a clean PHP process is started, and your application boots from 0. After the response is sent, the application gets completely destroyed.

I'm not proposing we ditch this battle-tested technique altogether; the fast request/response cycle is actually a critical part of the architecture I'll be describing. I do however want to point out some downsides:

Whenever a request arrives  

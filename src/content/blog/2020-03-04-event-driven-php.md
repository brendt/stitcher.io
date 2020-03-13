Lately I've been tinkering with a unique kind of architecture for PHP applications. I want to tell you up front that I don't think it will solve any real-life problems soon; still I want to involve you in the thought-process. Who knows what kind of great ideas might arise?

In this post I'll go through the architecture step-by-step and address its benefits as well as its downsides — at least, the ones I can think of right now. I do have a proof-of-concept codebase open sourced, and I'll share insights from it throughout this post.

{{ ad:carbon }}

So, first things first, what the architecture is about. It's a long-running PHP server, with its entire state loaded in memory, built from stored events. In other words: it's event sourcing as we know it in PHP, but all aggregates and projections are loaded in-memory and never stored on disk.

Let's break it down!

## A long-running PHP server

The first pillar of this architecture is a long running server. The modern PHP landscape offers several battle-tested solutions for managing these kinds of processes: frameworks like ReactPHP, Amphp and Swoole allowed the PHP community to venture into another, unexplored world; while day-to-day PHP was most often related to its characterizing fast request/response cycle.

This fast request/response cycle is of course one of the things that made PHP great: you never had to worry about leaking state or keeping everything in sync: when a request comes in, a clean PHP process is started, and your application boots from 0. After the response is sent, the application gets completely destroyed.

I'm not proposing we ditch this battle-tested technique altogether; the fast request/response cycle is actually a critical part of the architecture I'll be describing. On the other hand: always booting the whole application from scratch has its downsides. 

In the architecture I'm describing, an application is split into two parts: one part is a regular PHP app, accepting HTTP requests and generating responses; while the other part is a behind-the-scenes backend server that's always running. A server that always has the whole application state loaded in memory, which allows the clients — our regular PHP apps — to connect with it, read data and store events.

Because the whole application state is always loaded in memory, you never need to perform database queries, spending resources on mapping data from the database to objects, or performance issues like circular references between ORM entities.

This sounds nice in theory, but we probably still need to be able to perform complex queries, something that database are highly optimised for. It's clear that this architecture will require us to rethink about certain aspects we're used to in regular PHP applications. I'll come back to this later.

First, let's look at the second pillar: event-sourcing.

## Event sourcing 

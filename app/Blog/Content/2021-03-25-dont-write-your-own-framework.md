---
title: "Don't write your own framework"
next: dont-get-stuck
meta:
    description: 'How an in-house framework broke a few hundred websites'
---

<div class="sidenote">
<h2>You can listen to this post</h2>

If you prefer listening over reading, you can listen to this post [on YouTube](*https://www.youtube.com/watch?v=oy0b2U9fyRo&ab_channel=BrentRoose) or by subscribing to my podcast feed on [Apple Podcasts](*https://podcasts.apple.com/be/podcast/rant-with-brent/id1462956030), [Stitcher](*https://www.stitcher.com/s?fid=403581&refid=stpr.) or [Spotify](*https://open.spotify.com/show/43sF0kY3BWepaO9CkLvVdJ?si=R-MIXaMHQbegQyq3gQm7Yw)
</div>

We were sitting with 5 or 6 backend developers around the large meeting table. It was 10 in the morning on a Monday, and we were all silently working on our laptops. There was a hasty atmosphere, and everyone tried to concentrate on the task ahead.

Less than 2 hours before, I walked into the office, not yet aware of any harm. I was immediately called to the meeting room at the back, there was no time to sit at my desk. Still I quickly grabbed a coffee, and went to the back where a few other colleagues already gathered.

{{ ad:carbon }}

With them was our boss, a nice guy; there wasn't any "upper management" culture or anything, we were just colleagues. The other people in the room already knew what was going on, so he explained to me personally.

There was a bug, in our in-house framework. 

"It sure isn't the first one" — I remember thinking. 

At this point we'd used this custom-built framework for several years; 200 websites were affected, more or less.

The bug was a stupid mistake — after all, which bug isn't? 

Our framework router would take a URL and filter out repeated slashes, so `//admin` would become `/admin`. This is, I believe, part of some HTTP spec; at least I was told so, I never double checked. The problem however, was in the authorisation layer: `/admin` was a protected URL, but `//admin` was not. So the router would resolve `//admin` and all its underlying pages to the admin section, and the authoriser wouldn't recognise it as a location you'd need admin privileges for.

In other words: the admin section of all our websites could be entered without any login, by simply replacing `/admin` with `//admin`.

I don't remember drinking my coffee after that.

So we did the only thing we could do: manually update all of our websites, some running a very outdated version of our framework. It took us 3 days to do this with 5 or 6 developers. You can do the math on how much it cost.

In the end we never actually got to know whether the bug had been exploited: it was discovered by accident by one of our colleagues over the weekend, and we didn't keep access logs longer than a few days. So nobody could tell whether someone had unauthorised access to one of our sites over the past years; let alone know if, and which data had been leaked.

Don't write your own framework, at least not when you're building websites for paying clients; who trust your work to be professional and secure. Whatever framework you use, make sure it's backed by a large community.

Want to share your thoughts? Let's discuss them on [HN](*https://news.ycombinator.com/item?id=23508370).

{{ cta:diary }}

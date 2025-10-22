I did an interesting refactor on [Aggregate](https://aggregate.stitcher.io/), my community-driven content feed. Aggregate crawls content from across the web, and bundles it all in one RSS feed. Since I don't want to spam my subscribers with too much content at once, I have a "schedule feature" so that there will one be three new posts per day; if there are too many posts to publish, they will be queued for another day.

It all starts with a post's state: `PENDING`, `SCHEDULED`, or `PUBLISHED`. Transitioning from `PENDING` to another state is a manual user action (that's the whole point of Aggregate: it's hand-picked content), and then going from `SCHEDULED` to `PUBLISHED` is handled by a cron job (at least, it used to be; my refactor changed all of that).

If I described this "scheduling" problem from a human's point of view, it indeed makes sense: first a post is scheduled, then it becomes published at the right time. It's an automatic transition, driven by time, and adds some complexity to the codebase:

- A cron job that runs periodically to pick `SCHEDULED` posts and transition them to `PUBLISHED`;
- A way to interface between OS-level cron jobs and the code — likely a console command;
- A way to differentiate when a post needs to be `SCHEDULED` and when it needs to be `PUBLISHED` — in case there are no scheduled posts, and it can be published immediately; and
- Tests to verify that, one: the command correctly performs the state transition; and two: it correctly interacts with the OS-level cron so that it runs once per day.

Within this process, there is a lot of — what I like to call — _motion_ involved. There are many moving parts that have to work together: there's a cron job, a console command, another part dedicated to deciding whether the scheduled state can be skipped if there's room to do so. And with all these moving parts comes increased complexity for testing, maintaining, and debugging.

When I recently [rebuilt Aggregate with Tempest](/blog/whats-your-motivator), I had an opportunity to simplify this flow. I realized it would only take one change: I removed the `SCHEDULED` state, and added a `publicationDate` date to the `PUBLISHED` state. The trick that makes all of this work: a `publicationDate` can be in the future.

So whenever a post is published, I'll query the database to find the first "free slot":

```sql
SELECT {:hl-property:publicationDate:}
FROM {:hl-type:posts:}
WHERE 
    {:hl-property:publicationDate:} > :publicationDate
    AND {:hl-property:state:} = "PUBLISHED"
GROUP BY {:hl-property:publicationDate:}
HAVING COUNT(*) >= 3
ORDER BY {:hl-property:publicationDate:} DESC
```

This query gives the farthest day in the future that has three published posts or more, and then we add one more day to it:

```php
$nextAvailableDate = $futureDate->plusDay()->startOfDay();
```

And just like that, we've eliminated a lot of _motion_:

- There's no more cron job;
- There are no more automatic state transitions;
- There's no more need to differentiate between whether a post can be published instantly or not.

Of course, there are downsides to this solution as well: determining whether a post is "visible" becomes not only a check on its state but also on its publication date. There's the question of what to do when a future scheduled post gets removed and a new slot opens up "in between" (although, that one can be solved by adjusting the query).

Nevertheless, there are downsides that should be considered. We traded one kind of complexity for another kind. In my case though, the payoff is worth it. As with everything software-related, there are pros and cons to each approach — "it depends".

For me, the most important lesson is that modeling a process from the "human point of view" doesn't always lead to the best or simplest solution. Sometimes it's good to spend some time translating between "the human description", and "how to technically solve the problem", as they may not map one-to-one to each other.

Maybe you have some thoughts? For the very first time in the history of this blog, you can leave them here, right on this page ([simply scroll down](#comments)). If anything were to go wrong (because it's the first time 😅) you can still [send me an email](mailto:brendt@stitcher.io).
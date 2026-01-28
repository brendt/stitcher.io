---
title: Once again processing 11 million rows, now in seconds
---

Two weeks ago I embarked on a journey to optimize some PHP code when I had to process 11 million database-stored events. My initial code processed around 30 events per second, which meant the whole operation would take around 4 days to complete. In my last post, I managed to get that number [up to 50k events per second](/blog/processing-11-million-rows), resulting in only a couple of minutes of runtime.

<iframe width="560" height="345" src="https://www.youtube.com/embed/GY4RIj_zn1M" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

I knew there was still room for improvement though, and so, the journey begins again.

## Combined insert

This time we start with a much more acceptable baseline: **50k events per second**. After reading my blogpost, Márk reached out to me telling me my visits per year projector could be improved a lot. For reference: I'm keeping track of how many visits my blog got per year in this projector, and every time a visit event occurs. I was doing an individual insert query for every such visit, but all of that could be combined into one. So instead of doing this:

```sql
INSERT INTO {:hl-type:`visits_per_year`:} ({:hl-property:`date`:}, {:hl-property:`count`:}) VALUES ("2025-01-01", 1) {:hl-keyword:ON DUPLICATE KEY UPDATE:} {:hl-property:`count`:} = {:hl-property:`count`:} + 1;
INSERT INTO {:hl-type:`visits_per_year`:} ({:hl-property:`date`:}, {:hl-property:`count`:}) VALUES ("2025-01-01", 1) {:hl-keyword:ON DUPLICATE KEY UPDATE:} {:hl-property:`count`:} = {:hl-property:`count`:} + 1;
INSERT INTO {:hl-type:`visits_per_year`:} ({:hl-property:`date`:}, {:hl-property:`count`:}) VALUES ("2025-01-01", 1) {:hl-keyword:ON DUPLICATE KEY UPDATE:} {:hl-property:`count`:} = {:hl-property:`count`:} + 1;
INSERT INTO {:hl-type:`visits_per_year`:} ({:hl-property:`date`:}, {:hl-property:`count`:}) VALUES ("2025-01-01", 1) {:hl-keyword:ON DUPLICATE KEY UPDATE:} {:hl-property:`count`:} = {:hl-property:`count`:} + 1;
```

I would perform one query like so:

```sql
INSERT INTO {:hl-type:`visits_per_year`:} ({:hl-property:`date`:}, {:hl-property:`count`:}) 
    VALUES ("2025-01-01", 1), ("2025-01-01", 1), ("2025-01-01", 1), ("2025-01-01", 1) 
    {:hl-keyword:ON DUPLICATE KEY UPDATE:} {:hl-property:`count`:} = {:hl-property:`count`:} + 1;
```

With this change, performance doubled once again, from 50k to **100k events per second**!

## More with PHP

Did you spot that `{:hl-keyword:ON DUPLICATE KEY UPDATE:} {:hl-property:count:} = {:hl-property:count:} + 1`? That's MySQL's way of incrementing an integer on the database level when there's a duplicate key (in this table that would be the `{:hl-property:date:}` field).

Márk also mentioned that doing these calculations on the database level was far from optimal and suggested moving them to PHP. It required only a couple of small changes; first in the event handler itself:

```php
#[EventHandler]
public function onPageVisited(PageVisited $pageVisited): void
{
    $date = $pageVisited->visitedAt->format('Y') . '-01-01';

    {-$this->inserts[] = $date;-}
    $this->inserts[$date] = ($this->inserts[$date] ?? 0) + 1;
}
```

And then in the database query:

```php
public function persist(): void
{
    if ($this->inserts === []) {
        return;
    }

{+    $values = [];

    foreach ($this->inserts as $date => $count) {
        $values[] = "(\"{$date}-01-01\",{$count})";
    }

    $query = new Query(sprintf(
        'INSERT INTO `visits_per_year` (`date`, `count`) VALUES %s ON DUPLICATE KEY UPDATE `count` = `count` + VALUES(`count`)',
        implode(',', $values),
    ));+}

    $query->execute();

    $this->inserts = [];
}
```

You can imagine how this change has potential: instead of performing one huge insert query with thousands and thousands of values, we'd now only ever perform one update operation for every year. I ran the code, and my mind was blown — again — instead of 100k, we were now processing **250k events per second**. Now hold on, because there's a lot more to come — we're not even halfway done yet.

## Finetunings

Next, I made some smaller tweaks: upping the chunk limit, and only selecting the fields I actually needed from the `stored_events` table:

```php
{-$limit = 1500;-}
$limit = 30_000;

while ($events = query('stored_events')->select('id', 'createdAt')->where('id > ?', $lastId)->limit($limit)->all()) {
    // …
}
```

These changes added another 20k, pushing the result from 250k to **270k events per second**. Remember how 20k seemed like such a huge number back when I started this experiment? Now it felt like peanuts. Nevertheless, 20k is still 20k, and I'm happy with it!

## Let's think for a moment

At this point I could barely imagine any more possible improvements. The problem seemed pretty optimized: we're chunking both reads and writes and trying to minimize them as much as possible. We moved our calculations to PHP, which seemed to make a huge impact. What else could there be?

Well, after moving code back to PHP, I wondered… if we disregard I/O, what would — in theory — be the best possible time to process 11 million rows. Essentially, we're asking: what would the fastest possible time be in PHP to:

1. Process 11 million date strings to extract the year from it
2. Keep track of the number of visits per year in an array

Well, it's not too difficult to answer that question, right? Let's write a script for it!

```php
$start = microtime(true);

$inserts = [];

foreach (['2026-02-01 00:00:00', '2025-02-01 00:00:00', '2024-02-01 00:00:00', '2023-02-01 00:00:00', '2022-02-01 00:00:00', '2021-02-01 00:00:00'] as $date) {
    $i = 0;

    while ($i < 2_000_000) {
        $date = new DateTime($date)->format('Y') . '-01-01';
        $inserts[$date] ??= 0;
        $inserts[$date]++;
        $i++;
    }
}

$end = microtime(true);

echo $end - $start . 's';
```

Here we simulate 2M visits per year, for 6 years — about the same amount of data in my data set. Running this script takes 3.89 seconds on my machine. That means that — if we disregard I/O, my machine is able to process around 3M rows per second.

Now hang on, I wondered, is there something else that could be improved here? Well, to be honest, I don't know what `{php}new DateTime($date)->format('Y')` does under the hood, I might as well compare its performance to, for example, extracting the year from the raw string instead?

```php
while ($i < 2_000_000) {
    {-$date = new DateTime($date)->format('Y') . '-01-01';-}
    $date = substr($date, 0, 4) . '-01-01';
    $inserts[$date] ??= 0;
    $inserts[$date]++;
    $i++;
}
```

Now the script only took 0.49 seconds to run — a staggering 25M million rows per second!

Surely… there's more room for improvement then, no? Of course, in real life we're bound by I/O, but I could easily up the limit size of my query like I did before. But then what could be improved on the CPU side still?

## Going RAW

I decided to do an experiment. Let's ditch the only type of object we still have left: the event itself. There's nothing stopping us from using an array during replays? It will need a little bit more manual work on the projector side, but it's an experiment, right?

So instead of this:

```php
$events = arr($data)
    ->map(function (array $item) {
        return $item['eventClass']::unserialize($item['payload']);
    })
    ->toArray();
```

I would simply do this:

```php
$events = arr($data)
    ->map(function (array $item) {
        return json_decode($item['payload']);
    })
    ->toArray();
```

Of course, now my projector needs some work, because it only knows how to deal with a `{php}PageVisited` event object:

```php
 public function replay(array|object $event): void
{
    if (is_array($event)) {
        $this->onPageVisitedArray($event);
    } elseif ($event instanceof PageVisited) {
        $this->onPageVisited($event);
    }
}

// The event handler called at runtime
#[EventHandler]
public function onPageVisited(PageVisited $pageVisited): void
{
    $date = $pageVisited->visitedAt->format('Y') . '-01-01';

    $this->inserts[$date] = ($this->inserts[$date] ?? 0) + 1;
}

// The method called during replays, with raw array data
public function onPageVisitedArray(array $event): void
{
    $date = substr($event['createdAt'], 0, 4);

    $this->inserts[$date] = ($this->inserts[$date] ?? 0) + 1;
}
```

I don't particularly like this solution, but we're in an experimentation phase, remember? Let's see where it leads us. Remember we were at 270k events per second? This change bumped it to **400k events per second**.

## The final boss

At this point I'm like: we've probably reached the limit. I know PHP itself can process 25M events per second on my machine, but we're bound by I/O and memory. 

That's when I noticed one final optimization. What if — follow along for a second — what if we did not need to deserialize data anymore? Look, we're storing serialized events in the database and unserialize them on replay like so:

```php
$events = arr($data)
    ->map(function (array $item) {
        return json_decode($item['payload']);
    })
    ->toArray();
```

But actually… why? Why not store the event data straight in database columns? The answer, of course, is convenience. This `stored_events` table doesn't just store _one_ type of events. It's meant to store any event you'd like. Each type of event has different fields, so creating dedicated columns for each value wouldn't work. However, in our case, we only actually care about the _date_, and _that one particular field_ happens to be tracked as a separate column: `stored_events.createdAt`.

Let's just, just as an experiment, ditch that whole json_decode call and use `stored_events.createdAt` directly. What would happen then?

```php
while ($events = query('stored_events')->select('id', 'createdAt')->where('id > ?', $lastId)->limit($limit)->all()) {
    {-$events = arr($data)
        ->map(function (array $item) {
            return json_decode($item['payload']);
        })
        ->toArray();-}
}
```

I just want to point out that we started from processing 30 events per second, all the way up to 400k. It took a long way to get here. With this change, performance bumped to… **1.7M events per second**.

<video controls="true" autoplay="" muted="" loop="" playsinline="">
    <source src="/img/static/eps/eps-3.mp4" type="video/mp4">
</video>

Now, where does that leave us in practice, though? As you can imagine, having a dedicated table per event type is far from convenient, having to do all that manual work also isn't ideal. 

It's here that we arrive at a tricky balance: convenience vs. raw performance. If every second counts, you'll have to write some more code.

## Closing thoughts

I went full ballistic removing `{php}json_decode()`, but maybe there's a **more optimal way to serialize data in PHP**. Testing that theory would require me to migrate all that event data, which — from what I've learned during this journey — might take a while. I'm eager to try it out though, so if you have any ideas: let me know [in the comments](#comments) or [on Discord](/discord).

It's also worth mentioning that all of this is done on a single thread. **Parallel execution** has the potential to 10-20x this performance, depending on how many cores are available. There's an issue with parallel execution, though: given the nature of event sourcing, processing events in order is crucial. Sure, in our example with "visits per year", it doesn't matter if we process visits out of order. But say you want to track "sessions per year", where a "session" is defined by a single user visiting multiple pages over a span of time, then we can't just process the event stream in parallel, as it might lead to incorrect results. In fact, many event-sourced systems are chosen especially because that synchronous event stream opens a lot of possibilities.

That being said, parallel execution across projectors would be possible and would indeed speed up performance significantly. For me, my benchmarks were always done within the scope of one single projector, so it wouldn't be fair to introduce parallel execution now and say "I've improved performance 10-fold." That wouldn't be fair.

Despite all of that, I'm amazed by how far we've managed to come. Even with the most convenient approach, we're still finishing a replay in 40 seconds instead of 4 days. 

As a final thought, I want to say this. This was just one example; can you imagine how many more potential improvements there are in all of our codebases? Imagine being able to run your high-traffic website at 50% of the cost by optimizing your app's performance? There are real gains to be made, both financially and ecologically. Today more than ever — when more and more people don't seem to care about the quality of code anymore thanks to AI — I think that's an important question to ask. I still think programming is a beautiful craft that has the potential to do a lot of good, if only people are willing to put in the time and effort to truly understand what they are doing.
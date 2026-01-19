---
title: Processing 11 million rows in minutes instead of hours
---

Around 5 years ago, I decided to drop all client-side analytics on this blog, and instead opt for server-side anonymous analytics. I had several reasons:

- No more client-side overhead by removing JavaScript libraries
- Actually respecting my audience's privacy
- More accurate metrics (around 50% of visitors block client-side trackers)
- A fun challenge for me

The architecture is pretty straight forward: there's a long-running script on my server monitoring this blog's access log. It filters out crawlers and bot traffic, and stores real traffic in a database table. Since I want to generate graphs with this data, I opted to use [event sourcing](/blog/what-event-sourcing-is-not-about). Each visit is stored in the database, and that historic data is then processed by multiple projectors. Each projector is a unique interpretation of that source data. For example, the number of visits per day, per month, per post — whatever you like.

The biggest upside of event sourcing is that I can always add new graphs after the fact, and rebuild them from the same historic data. And that's exactly the performance problem I was optimizing this week. After five years, I've accumulated over 11 million visits on this blog. The code powering this system, however, was still running a very outdated Laravel installation. It was high time to convert it to Tempest, and to also fine-tune some of the older graphs.

After copying those 11 million rows to the new database, I'd have to rebuild all projections (the first time I'd be doing this since the start of the project), and I ran into a pretty significant performance issue: the replay command processed around 30 events per second per projector, so replaying those 11 million rows for a dozen or so projectors would take… around 50 hours. Yeah, I wasn't going to wait around that long. Here's what happened next.

## Establishing a baseline

Whenever I want to debug a performance issue, my first step is to set a baseline: the situation as it is right now. That way, I can actually measure improvements. My baseline is straightforward: **30 events per second**. Here's what the replay command looks like that I used to generate that baseline — I did strip away all the metric calculations, setup code, and added some comments for clarity:

```php
final readonly class EventsReplayCommand
{
    #[ConsoleCommand]
    public function __invoke(?string $replay = null): void
    {
        // Setup and metrics work, I've hidden this part
        
        foreach ($projectors as $projectorClass) {
            // We only run the selected projectors
            if (! in_array($projectorClass, $replay, strict: true)) {
                continue;
            }
            
            // We resolve a projector from the container
            $projector = $this->container->get($projectorClass);

            // Then we clear all its contents
            // to rebuild it from scratch
            $projector->clear();

            // We loop over all events,
            // sorted from old to new, chunked per 500
            StoredEvent::select()
                ->orderBy('createdAt ASC')
                ->chunk(
                    function (array $storedEvents) use ($projector): void {
                        foreach ($storedEvents as $storedEvent) {
                            // Each event is replayed on the projector
                            $projector->replay($storedEvent->getEvent());
                        }
                    },
                    500,
                );
        }
        
        // And we're done!
    }
}
```

Again: I've stripped the irrelevant parts so that you can focus on the code that matters. Our baseline of 30 events per second absolutely sucks, but that's exactly what we want to improve! Now the real work can start.

## No more sorting

A first step was to remove the sorting on `{:hl-property:createdAt:} {:hl-keyword:ASC:}`. Think about it: these events are already stored in the database sequentially, they are already sorted by time. Especially since `{:hl-property:createdAt:}` isn't an indexed column, I guessed this one change would already improve the situation significantly.

```php
// We loop over all events,
// sorted from old to new, chunked per 500
StoredEvent::select()
   {- ->orderBy('createdAt ASC') -}
    ->chunk(
```

Indeed, this change made our throughput jump from 30 to **6700 events per second**. You would think that's probably already the problem solved, but we'll actually be able to more than double that result in the end. 

## Reversing the loop

The next step doesn't really have a significant impact when running a single projector, but it does when running multiple ones. Our loop currently looks like this:

```php
// Loop over each projector
foreach ($projectors as $projector) {
    StoredEvent::select()
        ->orderBy('createdAt ASC')
        ->chunk(
            function (array $storedEvents) use ($projector): void {
                // Loop over each event
                foreach ($storedEvents as $storedEvent) {
                    // …
                }
            },
            500,
        );
    }
}
```

There's an obvious issue here: we'll retrieve the same events over and over again for each projector. Since events are the most numerous (millions of events compared to dozens of projectors), it makes a lot more sense to reverse that loop:

```php
StoredEvent::select()
    ->orderBy('createdAt ASC')
    ->chunk(
        function (array $storedEvents) use ($projectors): void {
            // Loop over each projector
            foreach ($projectors as $projector) {
                // Loop over each event
                foreach ($storedEvents as $storedEvent) {
                    // …
                }
            }
        },
        500,
    );
```

Because my baseline was scoped to one projector, I didn't expect to gain much from this change. Still, I made sure it also didn't negatively impact my already established improvement. It didn't: it went from 6700 to **6800 events per second**.  

## Ditch the ORM

The next improvement would again be a significant leap. Because we're dealing with so many events, it's reasonable to assume the ORM will have a measurable impact. As a test, I switched to a raw query builder instead. It required a bit more manual mapping work, but the results were significant:

```php
{-StoredEvent::select()-}
query('stored_events')
    ->select()
    ->chunk(function (array $data) use ($projectors) {
        // Manually map the data to event classes
        $events = arr($data)
            ->map(function (array $item) {
                return $item['eventClass']::unserialize($item['payload']);
            })
            ->toArray();
        
        // Loop over each projector
        foreach ($projectors as $projector) {
            // Loop over each event
            foreach ($storedEvents as $storedEvent) {
                // …
            }
        }
    )};
```

A jump from 6.8k to **7.8k events per second**! Not unsurprisingly. I have no objection to using an ORM for convenience, but convenience always comes at a cost. Dealing with these amounts of data, I rather reduce as many layers as possible to stay as close to the database as possible.

## Even more decoupling

Having seen such an improvement by ditching the ORM, I wondered if removing the `{php}chunk()` method which uses closures, and replacing it with a normal `{php}while` loop would have an impact. Again a little more manual work was required, but…

```php
$offset = 0;
$limit = 1500;

{-query('stored_events')->chunk(-}
        
while ($data = query('stored_events')->select()->offset($offset)->limit($limit)->all()) {
    // Manually map the data to event classes
    $events = arr($data)
        ->map(function (array $item) {
            return $item['eventClass']::unserialize($item['payload']);
        })
        ->toArray();
        
    // Loop over each projector
    foreach ($projectors as $projector) {
        // Loop over each event
        foreach ($storedEvents as $storedEvent) {
            // …
        }
    }
    
    $offset += $limit;
}
```

Another small adjustment you might have noticed is that I adjusted the limit size from 500 to 1500. Trying out multiple options in my environment, this yielded the best result. These changes improved performance from 7.8k to **8.4k events per second**.

## Faster serialization?

Because we want to store all kinds of events and not necessarily only page visits, the actual event data is serialized in the database. That's why we have to unserialize it like so:

```php
$events = arr($data)
    ->map(function (array $item) {
        return $item['eventClass']::unserialize($item['payload']);
    })
```

I wondered whether there would be any improvements if I manually created a new event instead of unserializing an existing one. In the old Laravel version, event data was stored directly in a `visits` table, which meant I could access that raw data directly as well:

```php
while ($data = query('visits')->select()->offset($offset)->limit($limit)->all()) {
    $events = arr($data)
        ->map(function (array $item) {
            return new PageVisited(
                url: $item['url'],
                visitedAt: new \DateTimeImmutable($item['date']),
                ip: $item['ip'],
                userAgent: $item['user_agent'] ?? '',
                raw: $item['payload'],
            );
        })
        ->toArray();

    // …
}
```

Surprisingly, though, this change had a negative impact. I reckon PHP's unserialization might be extra optimized. So I decided to not go this route. At this point, I wondered whether I had reached the limit. I wanted to be sure, though, that there were no bottlenecks left. So I decided to boot up the good ol' Xdebug and ran a profiler.

## Discovering a framework bug

Running the profiler, I saw something odd:

[![](/img/blog/eps/benchmark-1.png)](*/img/blog/eps/benchmark-1.png)

For one iteration (so for 1.5k events), Tempest was calling `{php}TypeReflector` 175,000 times. Now, this is a wrapper class around PHP's reflection API, which is heavily used in the ORM. But I already removed the ORM, didn't I? First, I checked whether the projector itself might still be using the ORM, but that wasn't the case:

```php
final readonly class VisitsPerYearProjector implements Projector
{
    // …
    
    #[EventHandler]
    public function onPageVisited(PageVisited $pageVisited): void
    {
        $date = $pageVisited->visitedAt->format('Y') . '-01-01';

        new Query(<<<SQL
        INSERT INTO `visits_per_year` (`date`, `count`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `count` = `count` + 1
        SQL, [
            $date,
            1
        ])->execute();
    }
}
```

What could it then be? Of course, the profiling data showed the culprit: `{php}GenericDatabase` itself was using the `{php}SerializerFactory` to prepare data before inserting it into the database. This factory uses reflection to determine what kind of serializer should be used for a given value:

```php
private function resolveBindings(Query $query): array
{
    $bindings = [];

    $serializerFactory = $this->serializerFactory->in($this->context);

    foreach ($query->bindings as $key => $value) {
        if ($value instanceof Query) {
            $value = $value->execute();
        } elseif ($serializer = $serializerFactory->forValue($value)) {
            $value = $serializer->serialize($value);
        }

        $bindings[$key] = $value;
    }

    return $bindings;
}
```

However, in our case, we _know_ we're dealing with scalar data already, and we're certain the database won't need any serializer. That was an easy fix [on the framework level](https://github.com/tempestphp/tempest-framework/pull/1898):

```php
if ($value instanceof Query) {
    $value = $value->execute();
{+} elseif (is_string($value) || is_numeric($value)) {
    // Keep value as is
+}} elseif ($serializer = $serializerFactory->forValue($value)) {
    $value = $serializer->serialize($value);
}
```

This small change actually got the most impressive improvement: performance jumped from 8.4k to **14k events per second**!

Running another profiler session also confirmed: there were no more big bottlenecks to see!

[![](/img/blog/eps/benchmark-2.png)](*/img/blog/eps/benchmark-2.png)

## One more thing!

One last thing I noticed only when I ran the replay command for a longer time was that performance would slow down the longer the code ran. I was already tracking memory, which remained stable, so that wasn't the problem. After some thought, I realized it might have to do with the increasing `$offset` size on the query. So instead of using a traditional offset, I swapped it for the numeric (indexed) ID:

```php
$lastId = 0;
$limit = 1500;

while ($data = query('stored_events')->select()->where('id > ?', $lastId)->limit($limit)->all()) {
    // …
    
    $lastId = array_last($data)['id'];
}
```

This kept performance steady over time, which is rather important because our script will run for tens of minutes.

## Finally satisfied

With these results, and confirmed by Xdebug, I was satisfied: I've managed to optimize performance from 30 to 14,000 events per second. Rebuilding a single projector now takes 10 minutes instead of 4–5 hours, an amazing improvement.

However, I'm sure there are more improvements to be made. Maybe you have some ideas as well? I'd love to read them in [the comments](#comments)!

Finally, all the code for this is open source. You can check of my [blog's source code here](https://github.com/brendt/stitcher.io), [the analytics module is here](https://github.com/brendt/stitcher.io/tree/main/app/Analytics), [the dashboard itself is here](/analytics), and it's of course all powered by [Tempest](https://tempestphp.com/). 
Hi ::subscriber.first_name::

I did something new these past two weeks: I sat down and read through RFCs — nothing new yet — while recording and sharing my thoughts. It was pretty fun to do, and viewers on YouTube seemed to really like the format. I plan on making some more of these videos the coming weeks, but I already wanted to share some highlights from them with you.

If you want to take a look at the recordings, by the way, you can check out the playlist here: [https://www.youtube.com/playlist?list=PL0bgkxUS9EaKNWvKhX_QAiX4vJYLAz7nX](https://www.youtube.com/playlist?list=PL0bgkxUS9EaKNWvKhX_QAiX4vJYLAz7nX).

## HTML 5 in PHP 8.4

The first big thing in PHP 8.4 must be HTML 5 support. Granted, it took them a while to add — 16 years — but this one will be super useful. What's especially nice about this RFC is that the old HTML parser is still available, nothing breaks. Furthermore, the new parser will parse HTML into the same type of value objects (`DOMNode`, `DOMElement`, …), so in theory, you should be able to replace the parser, and not touch the rest of your code!

```php
$dom = \Dom\HTMLDocument::createFromString($html); 
```

You can read more about the details on [my blog](https://aggregate.stitcher.io/post/da09d655-520e-43d3-9bd7-300fa654aaf3).

## array_find

Next, there's a pretty simple new function added in PHP 8.4, one of those functions that you have to wonder about "hang on, wasn't that available yet?" I guess most developers have grown used to third party collection classes, although I think having `array_find` natively in PHP is pretty nice. 

The naming might be a bit confusing though, because what this function does is it takes an array and callback, and will return the _first_ element for which the callback returns `true`:

```php
$firstMatch = array_find(
    $posts, 
    function (Post $post) {
        return strlen($post->title) > 5; 
    }
);
```

There are also some variations of this function called `array_find_key`, `array_any` and `array_all`, you can [read with me through the RFC](https://aggregate.stitcher.io/post/f0061cbc-97fa-410a-a162-e439bc4714ce) to learn more about it.

## New without parentheses

Finally, I checked out the [new without parentheses RFC](https://aggregate.stitcher.io/post/a30d49b5-bc8d-43cc-b1b3-0f5861ee2693), which basically allows you to omit brackets surrounding newly created objects in order to chain methods or properties on them.

It's a tiny change, but one that'll have a pretty big impact. So instead of writing this:

```php
(new ReflectionClass($className))->getShortName();
```

You can now write this:

```php
new ReflectionClass($className)->getShortName();
```

--- 

That are all the RFCs I looked at last week, although I am working on a couple more videos and blogposts, so stay tuned! As a final, personal note, I published the first chapter of that sci-fi novel I'm writing (I mentioned it in the previous newsletter). In case you want to check it out, here's [chapter 1](https://aggregate.stitcher.io/post/18478d82-bfc7-49ec-81d5-393f0c313a85). 

Until next time!

Brent
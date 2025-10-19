Ok so, this is an important topic. There's a new RFC: [Interface Default Methods](https://aggregate.stitcher.io/post/adf2ee9f-095f-478a-8700-f7a2ecbe8b1a). Basically, it allows you to provide default implementations for interface methods, _within_ the interface:

```php
interface Clickable 
{
    public function handleClick(
        <hljs type>Point</hljs> $point, 
        <hljs type>Game</hljs> $game
    ): <hljs type>void</hljs> {
        $game-><hljs prop>dispatch</hljs>(new <hljs type>HandleClick</hljs>($this));
    }
}
```

The reasoning of the RFC is that it allows for easier additions and changes to existing interfaces. A valid use case, but I'm most excited about how it allows a form of multi-inheritance.

Of course, we can already do a kind of multi-inheritance thanks to traits. The problem right now is that you cannot type hint for traits, and so you have to manually attach an interface to it. You might have seen this pattern before:

```php
interface Clickable 
{
    public function handleClick(
        <hljs type>Point</hljs> $point, 
        <hljs type>Game</hljs> $game
    ): <hljs type>void</hljs>;
}

trait <hljs type>ClickableTrait</hljs>
{
    public function handleClick(
        <hljs type>Point</hljs> $point, 
        <hljs type>Game</hljs> $game
    ): <hljs type>void</hljs> {
        $game-><hljs prop>dispatch</hljs>(new <hljs type>HandleClick</hljs>($this));
    }
}

final <hljs keyword>readonly</hljs> class Tile implements Clickable
{
    use <hljs type>ClickableTrait</hljs>;
}
```

I like this interface default methods, because it gets rid of all this unnecessary boilerplate:


```php
interface Clickable 
{
    public function handleClick(
        <hljs type>Point</hljs> $point, 
        <hljs type>Game</hljs> $game
    ): <hljs type>void</hljs> {
        $game-><hljs prop>dispatch</hljs>(new <hljs type>HandleClick</hljs>($this));
    }
}

final <hljs keyword>readonly</hljs> class Tile implements Clickable
{
}
```

Of course, there are some side notes: interfaces don't support properties, so if you need to rely on internal state, you'll have to provide dedicated methods for it. That's a major drawback. Maybe, it would be better if we'd be able to type hint traits directly instead?

Food for thought. 

In any case, I think this RFC is very interesting, and has lots of potential. *However*, several people find this proposal very strange: an interface shouldn't provide an implementation, right? Well, I've made a video about these concerns, and I would really like to read your comments on it as well!

<p>
<a href="https://aggregate.stitcher.io/post/4ca4c3c9-3ae0-4d14-8c9b-671da41b13ca">
<img src="https://stitcher.io/resources/img/static/multi-inheritance.png" alt="Multi Inheritance video thumbnail" />

<span>
Video: Multi Inheritance in PHP
</span>
</a>
</p>

Don't hesitate to hit reply to share your thoughts!

Until next time

Brent
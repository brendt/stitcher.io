I'm working on a new feature for our [Spatie](*https://spatie.be) website: I'm adding a lightweight form of gamification for our ±10,000 users. 

Here's how it works: every time a user watches one of our course videos, every time they complete a video series or a pull request gets merged on GitHub, they'll receive experience points. On top of that, there are also some achievements to reward users for their efforts. For example there's a "10 pull requests" achievement, as well as a "100 XP" achievement and a few others. 

Getting an achievement will reward the user with a digital badge, and in some cases they'll get some kind of certificate as well.

As you can see, it's a small, contained system, with well defined boundaries. And I'd like to discuss one aspect of it with you today.

---

Let's zoom in on one of the possible flows: the pull request reward system. There are a few steps to it:

- Register a user's pull request
- Award some XP for it
- Check whether this pull request is eligible for an achievement
- If that's the case, unlock a badge and notify the user

Writing this flow down in bullets makes it feel like the pull request reward system is a linear process. In fact, we could write it like so:

```php
class RegisterPullRequest
{
    public function __invoke(<hljs type>PullRequestData</hljs> $data): void
    {
        // Persist the pull request
        $pullRequest = <hljs type>PullRequest</hljs>::<hljs prop>create</hljs>(...$data);
        
        $user = $pullRequest-><hljs prop>user</hljs>;
        
        // Award XP
        $user-><hljs prop>award</hljs>(10);
        
        $pullRequestCount = <hljs prop>count</hljs>($user-><hljs prop>pullRequests</hljs>);
        
        // Determine whether an achievement should be triggered
        if (<hljs prop>in_array</hljs>($pullRequestCount, [10, 50, 100])) {
            $achievement = new <hljs type>PullRequestAchievement</hljs>($pullRequestCount);
            
            // Persist the achievement  
            $user-><hljs prop>unlock</hljs>($achievement);
            
            // Notify the user
            $user-><hljs prop>notify</hljs>(new <hljs type>AchievementNotification</hljs>($achievement));
        }
    }
}
```

You might want to refactor this code to separate methods or to use injected actions or service classes if you prefer that style of programming; but I wanted to keep this example concise, to easily clarify a flaw with this approach.

This code clearly represents the ordered steps we listed at first, but there are some hidden costs that come with it; costs that might not be apparent at the time of writing it. I can see two problems hidden within this implementation:

- We've hard-coded the flow of our program into a fixed order
- We've mixed a bunch of responsibilities into one giant class

Let's consider "awarding XP" and "unlocking achievements" for a moment. These are two equally important parts of our system. In fact, there's also an achievement for XP being awarded, which means that our current implementation is either lacking, or that there's some added functionality in `$user-><hljs prop>award</hljs>(10);` that we don't know about. Let's assume the latter for now.

Even though these two parts are equally important and not directly dependant on each other, we've combined them into one process because it seems like they belong together. However, an unfortunate side effect of doing so, is that our `<hljs type>RegisterPullRequest</hljs>` class is growing larger and more complex. Making a change to how pull request achievements are handled, will inevitably take us to the same place where XP rewards are handled.

While you might find it still relatively easy to reason about this isolated (simplified) example, I think most of us can agree that yes, in fact, we're mixing several processes together into one: we're creating some kind of "god-class" that manages and oversees a complex process. We've created a single point of failure. And the more complex our business becomes, this code has the potential to grow larger, complexer and more difficult to reason about.

Speaking for myself, I've written these kinds of classes more than I'd like to admit, and I've seen it applied in many other code bases as well. And from experience, I can tell you they grow much larger than the example we're working with today.

I understand why we get to this point: we'll always need some kind of _entry-point_ in our code, no? A complex process will need to be tied together somehow; we can't avoid that, right?

{{ cta:mail }}

When I first learned about event-driven systems, I was hesitant, maybe even skeptical about them. Events introduce an unavoidable layer of indirectness to our code that makes it more difficult to follow the "flow of our program".  However, keeping everything tightly coupled together _also_ makes it difficult to understand our program flow, just in another way.

The indirectness of event-driven systems is actually the solution to our problem. While event-driven architecture might feel overly complex at first glance, it offers exactly the kind of flexibility we need to model our processes in a clean way — and better yet: in a way that's scalable and maintainable for years to come, much more than our current solution.  

In an event-driven system, both "XP rewards" and "achievement unlocks" are treated as two standalone systems. They don't need to know of each other. The only thing they need to know is when a pull request is merged — when an event happens.

Both our systems are now event listeners that will act whenever a `<hljs type>PullRequestMerged</hljs>` event is dispatched:

```php
class AchievementManager
{
    public function __invoke(<hljs type>PullRequestMerged</hljs> $event): void
    {
        $pullRequestCount = <hljs type>User</hljs>::<hljs prop>find</hljs>($event-><hljs prop>userId</hljs>)
            -><hljs prop>pullRequests</hljs>
            -><hljs prop>count</hljs>();
        
        if (! <hljs prop>in_array</hljs>($pullRequestCount, [10, 50, 100])) {
            return;
        }
        
        $achievement = new <hljs type>PullRequestAchievement</hljs>($pullRequestCount);
        
        $user-><hljs prop>unlock</hljs>($achievement);
        
        $user-><hljs prop>notify</hljs>(new <hljs type>AchievementNotification</hljs>($achievement));
    }
}
```

```php
class ExperienceManager
{
    public function __invoke(<hljs type>PullRequestMerged</hljs> $event): void
    {
        $user = <hljs type>User</hljs>::<hljs prop>find</hljs>($event-><hljs prop>userId</hljs>);
        
        $user-><hljs prop>award</hljs>(10);
    }
}
```

Now that these two systems are separated, it's much easier to reason about them because they live in isolation. 

It doesn't stop there by the way. What about that "achievement for a given amount of XP" I mentioned at the beginning of this post? `<hljs type>ExperienceEarned</hljs>` could be an event itself that our `<hljs type>AchievementManager</hljs>` listens for as well:

```php
class AchievementManager
{
    public function onPullRequestMerged(<hljs type>PullRequestMerged</hljs> $event): void
    { /* … */ }
    
    public function onExperienceEarned(<hljs type>ExperienceEarned</hljs> $event): void
    {
        $user = <hljs type>User</hljs>::<hljs prop>find</hljs>($event-><hljs prop>userId</hljs>);
        
        $currentCount = $user->experience;
        
        $previousCount = $currentCount - $event->amount;
        
        if ($previousCount >= 100) {
            return;
        }
        
        if ($currentCount < 100) {
            return;
        }
        
        $achievement = new <hljs type>ExperienceAchievement</hljs>('100 XP!');
        
        $user-><hljs prop>unlock</hljs>($achievement);
        
        $user-><hljs prop>notify</hljs>(new <hljs type>AchievementNotification</hljs>($achievement));
    }
}
```

You might even begin to see some opportunities yourself: what about sending a mail after an achievement was unlocked? That could also be driven by an event, so that `<hljs type>AchievementManager</hljs>` doesn't need to think about it — we could add a listener that handles mails. What about persisting the pull request to the database? That could be event-driven as well. Achievements that earn experience? The list goes on.

This is the beauty of event-driven systems: by removing tightly-coupled components, we allow room for much more flexibility, while keeping our individual components small and sustainable. Besides that, events are an excellent way of handling micro-service messaging, horizontal scaling and more — though, discussing all these benefits would be too much to cover in one blog post.

Of course, I'm also glossing over some important details: what about eventual consistency? Or what about persisting events themselves? There's much more to event-driven systems than what I showed today, but I _did_ show you the power of thinking with events. That idea alone has revolutionized the way I look at code, and I hope you will give it some more thought as well.

{{ cta:mail }}

If you're really interested in the topic, I'd like to share my course on [event sourcing in Laravel](*https://event-sourcing-laravel.com/) with you. It's an in-depth course that covers many topics related to event-driven systems, and you don't need any prior Laravel or PHP knowledge to learn tons of stuff from it. You can read [a sample chapter](*https://event-sourcing-laravel.com/starting-with-event-sourcing) or [two](*https://event-sourcing-laravel.com/projectors-in-depth) if you'd like to know more.  

With all of that being said, let me know your thoughts on [Twitter](*https://twitter.com/brendt_gd) and leave a like if you appreciated this post, thanks!

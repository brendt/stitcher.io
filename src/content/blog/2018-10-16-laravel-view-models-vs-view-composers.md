<div class="author">
    Update: I've written a new version of this post, as part of my <a href="/blog/laravel-beyond-crud">Laravel beyond CRUD</a> series. You can read it <a href="/blog/laravel-beyond-crud-08-view-models">here</a>. 
</div>

Last month I wrote about view models in Laravel. 
I received a lot of good reactions on the post, but also the same question over and over again:
how do view models differ from view composers in Laravel?

Time to clarify this question once and for all.

{{ ad:carbon }}

## View composers

Let's look at how view composers are used in Laravel. 
View composers are a way of binding data to a view from global configuration.

The Laravel documentation explains it like this:

> View composers are callbacks or class methods that are called when a view is rendered. 
> If you have data that you want to be bound to a view each time that view is rendered, 
> a view composer can help you organize that logic into a single location.
 
View composers are registered like this, the example is taken from the Laravel docs.

```php
class ComposerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer(
            'profile', ProfileComposer::class
        );

        View::composer('dashboard', function ($view) {
            // …
        });
    }
    
    // …
}
```

As you can see you can both use a class and a closure which you can use to add variables to a view.

Here's how view composers are used in controllers.

```php
class ProfileController
{
    public function index()
    {
        return new view('profile');
    }
}
```

Can you see them? Nope, of course not: view composers are registered somewhere in the global state, 
and you don't know which variables are available to the view, without that implicit knowledge.

Now I *know* that this isn't a problem in small projects. 
When you're the single developer and only have 20 controllers and maybe 20 view composers, 
it'll all fit in your head.

But what about a project with three or four developers, with hundreds of controllers?
What if you're taking over a legacy project where you don't have this implicit knowledge?

This is why at [Spatie](*https://spatie.be), we use view models in our larger projects.
They make everything much more explicit, which helps us keep the code maintainable.

Here's what we do:

```php
class ProfileController
{
    public function index(User $user)
    {
        return new view(
            'profile', 
            new ProfileViewModel($user)
        );
    }
}
```

Now it's clear now from the controller itself what variables are available to the view.
We can also re-use the same view for multiple contexts. 
An example would be the same form view used in the create and edit actions. 

One last added benefit, one you might not have thought about, 
is that we can pass data into the view model explicitly. 
If you want to use a route argument or bound model to determine data passed to the view,
it is done explicitly.

In conclusion: managing global state is a pain in large applications,
especially when you're working with multiple developers on the same project.
Also remember that just because two means have the same end result,
that doesn't mean that they are the same!

I hope this quick writeup answers all the questions about the difference between view models and -composers.
If you want to know more about view models in particular, 
be sure to read the blog post about them [here](/blog/laravel-view-models).

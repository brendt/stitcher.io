In this chapter of my [Laravel beyond CRUD](/blog/laravel-beyond-crud) series, we take a deep dive in the application layer. A major trend throughout the series is to keep code clean, concise and manageable. This chapter won't be any different, as we'll look at how to keep controllers clean and to-the-point. 

The pattern we'll use to help us is called the view model pattern.
As its name suggests, these classes are models to your view files; they are responsible for providing data to a view, which would otherwise come directly from the controller or the domain model.
They allow a better separation of concerns, and provide more flexibility for the developer.

In essence, view models are simple classes that take some data, 
and transform it into something usable for the view.
In this chapter I'll show you the basic principles of the pattern, 
we'll take a look at how they integrate in Laravel projects,
and finally I'll show you how we use the pattern in one of our projects.

{{ ad:carbon }}

Let's get started. 

Say you have a form to create a blog post with a category.
You'll need a way to fill the select box in the view with category options. 
The controller has to provide those.

```php
public function create()
{
    return <hljs prop>view</hljs>('blog.form', [
        'categories' => <hljs type>Category</hljs>::<hljs prop>all</hljs>(),
    ]);
}
```
 
The above example works for the create method, 
but let's not forget we should also be able to edit existing posts.

```php
public function edit(<hljs type>Post</hljs> $post)
{
    return <hljs prop>view</hljs>('blog.form', [
        'post' => $post,
        'categories' => <hljs type>Category</hljs>::<hljs prop>all</hljs>(),
    ]);
}
```

Next there's a new business requirement: 
users should be restricted in which categories they are allowed to post in.
In other words: the category selection should be restricted based on the user.

```php
return <hljs prop>view</hljs>('blog.form', [
    'categories' => <hljs type>Category</hljs>::<hljs prop>allowedForUser</hljs>(
        <hljs prop>current_user</hljs>()
    )-><hljs prop>get</hljs>(),
]);
```

This approach doesn't scale. 
You'll have to change code both in the `create` and `edit` method.
Can you imagine what happens when you need to add tags to a post?
Or if there's another special admin form for creating and editing posts?

The next solution is to have the post model itself provide the categories, like so:

```php
class Post extends Model
{
    public static function allowedCategories(): Collection 
    {
        return <hljs type>Category</hljs>::<hljs prop>query</hljs>()
            -><hljs prop>allowedForUser</hljs>(<hljs prop>current_user</hljs>())
            -><hljs prop>get</hljs>();
    }
}
```

There are numerous reasons why this is a bad idea, though it happens often in Laravel projects.
Let's focus on the most relevant problem for our case: it still allows for duplication.

Say there's a new model `News` which also needs the same category selection.
This again causes duplication, but on the model level instead of in the controllers.

Another option is to put the method on the `User` model.
This makes the most sense, but also makes maintenance harder.
Imagine we're using tags as mentioned before. 
They don't rely on the user. 
Now we need to get the categories from the user model, and tags from somewhere else.

I hope it's clear that using models as data providers for views also isn't the silver bullet.

In summary, wherever you try to get the categories from, 
there always seems to be some code duplication.
This makes it harder to maintain and reason about the code.

This is where view models come into play. 
They encapsulate all this logic so that it can be reused in different places.
They have one responsibility and one responsibility only: providing the view with the correct data.

```php
class PostFormViewModel
{
    public function __construct(<hljs type>User</hljs> $user, <hljs type>Post</hljs> $post = null) 
    {
        $this->user = $user;
        $this->post = $post;
    }
    
    public function post(): Post
    {
        return $this->post ?? new <hljs type>Post</hljs>();
    }
    
    public function categories(): Collection
    {
        return <hljs type>Category</hljs>::<hljs prop>allowedForUser</hljs>($this->user)-><hljs prop>get</hljs>();
    }
}
```

Let's name a few key features of such a class:

- All dependencies are injected, this gives the most flexibility to the outside context.
- The view model exposes some methods that can be used by the view.
- There will either be a new or existing post provided by the `post` method, 
depending on whether you are creating or editing a post.

This is what the controller looks like:

```php
class PostsController
{
    public function create()
    {
        $viewModel = new <hljs type>PostFormViewModel</hljs>(
            <hljs prop>current_user</hljs>()
        );
        
        return <hljs prop>view</hljs>('blog.form', <hljs prop>compact</hljs>('viewModel'));
    }
    
    public function edit(Post $post)
    {
        $viewModel = new <hljs type>PostFormViewModel</hljs>(
            <hljs prop>current_user</hljs>(), 
            $post
        );
    
        return <hljs prop>view</hljs>('blog.form', <hljs prop>compact</hljs>('viewModel'));
    }
}
```

And finally, it can be used in the view like so:

```txt
<<hljs keyword>input</hljs> <hljs prop>value</hljs>="{{ $viewModel-><hljs prop>post</hljs>()->title }}" />
<<hljs keyword>input</hljs> <hljs prop>value</hljs>="{{ $viewModel-><hljs prop>post</hljs>()->body }}" />

<<hljs keyword>select</hljs>>
    @<hljs type>foreach</hljs> ($viewModel-><hljs prop>categories</hljs>() as $category)
        <<hljs keyword>option</hljs> <hljs prop>value</hljs>="{{ $category->id }}">
            {{ $category->name }}
        </<hljs keyword>option</hljs>>
    @<hljs type>endforeach</hljs>
</<hljs keyword>select</hljs>>
```

## View models in Laravel

The previous example showed a simple class with some methods as our view model.
This is sufficient to use the pattern,
but within Laravel projects there are a few more niceties we can add.

For example, you can pass a view model directly to the `view` function if the view model implements `Arrayable`. 

```php
public function create()
{
    $viewModel = new <hljs type>PostFormViewModel</hljs>(
        <hljs prop>current_user</hljs>()
    );
    
    return <hljs prop>view</hljs>('blog.form', $viewModel);
}
```

The view can now directly use the view model's properties like `$post` and `$categories`.
The previous example now looks like this:

```txt
<<hljs keyword>input</hljs> <hljs prop>value</hljs>="{{ $post->title }}" />
<<hljs keyword>input</hljs> <hljs prop>value</hljs>="{{ $post->body }}" />

<<hljs keyword>select</hljs>>
    @<hljs type>foreach</hljs> ($categories as $category)
        <<hljs keyword>option</hljs> <hljs prop>value</hljs>="{{ $category->id }}">
            {{ $category->name }}
        </<hljs keyword>option</hljs>>
    @<hljs type>endforeach</hljs>
</<hljs keyword>select</hljs>>
``` 

You can also return the view model itself as JSON data, by implementing `Responsable`. 
This can be useful when you're saving the form via an AJAX call, 
and want to repopulate it with up-to-date data after the call is done. 

```php
public function update(<hljs type>Request</hljs> $request, <hljs type>Post</hljs> $post)
{
    // Update the post…

    return new <hljs type>PostFormViewModel</hljs>(
        <hljs prop>current_user</hljs>(),
        $post
    );
}
```

You might see a similarity between view models and Laravel resources.
Remember that resources map one-to-one on a model, whereas view models may provide whatever data they want.

In our projects, we're actually using resources and view models combined:

```php
class PostViewModel
{
    // …
    
    public function values(): array
    {
        return <hljs type>PostResource</hljs>::<hljs prop>make</hljs>(
            $this->post ?? new <hljs type>Post</hljs>()
        )-><hljs prop>resolve</hljs>();
    }
}
```

Finally, in this project we're working with Vue form components, which require JSON data.
We've made an abstraction that provides this JSON data instead of objects or arrays, 
when calling the magic getter:

```php
abstract class ViewModel
{
    // …
    
    public function __get($name): ?string
    {
        $name = <hljs type>Str</hljs>::<hljs prop>camel</hljs>($name);
    
        // Some validation…
    
        $values = $this->{$name}();
    
        if (! <hljs prop>is_string</hljs>($values)) {
            return <hljs prop>json_encode</hljs>($values);
        }
    
        return $values;
    }
}
```

Instead of calling the view model methods, we can call their properties and get JSON back.

```txt
<<hljs keyword>select-field</hljs>
    <hljs prop>label</hljs>="{{ __('Post category') }}"
    <hljs prop>name</hljs>="post_category_id"
    :<hljs prop>options</hljs>="{{ $postViewModel->post_categories }}"
></<hljs keyword>select-field</hljs>>
```

## Wait, what about view composers?

You might be thinking there's some overlap with Laravel's view composers, but don't be mistaken. The Laravel documentation explains view composers like so:

> View composers are callbacks or class methods that are called when a view is rendered.  If you have data that you want to be bound to a view each time that view is rendered, a view composer can help you organize that logic into a single location.
 
View composers are registered like this (the example is taken from the [Laravel docs](https://laravel.com/docs/6.x/views#view-composers)):

```php
class ViewComposerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        <hljs type>View</hljs>::<hljs prop>composer</hljs>(
            'profile', <hljs type>ProfileComposer</hljs>::class
        );

        <hljs type>View</hljs>::<hljs prop>composer</hljs>('dashboard', function ($view) {
            // …
        });
    }
    
    // …
}
```

As you can see, you can both use a class and a closure which you can use to add variables to a view.

Here's how view composers are used in controllers.

```php
class ProfileController
{
    public function index()
    {
        return <hljs prop>view</hljs>('profile');
    }
}
```

Can you see them? Nope, of course not: view composers are registered somewhere in the global state, 
and you don't know which variables are available to the view, without that implicit knowledge.

Now I *know* that this isn't a problem in small projects. 
When you're the only developer and have 20 controllers and maybe 20 view composers, 
it'll all fit in your head.

But what about the kind of projects we're writing about in this series? When you're working with several developers, in a codebase that counts thousands upon thousands lines of code. It won't fit in your head anymore, not on that scale; let alone your colleagues also having the same knowledge.
That's why the view model pattern is the preferred approach. It makes clear from the controller itself what variables are available to the view. On top of that, you can re-use the same view model for multiple contexts. 

One last benefit — one you might not have thought about —  
is that we can pass data into the view model explicitly. 
If you want to use a route argument or bound model to determine data passed to the view,
it is done explicitly.

In conclusion: managing global state is a pain in large applications,
especially when you're working with multiple developers on the same project.
Also remember that just because two means have the same end result,
that doesn't mean that they are the same!

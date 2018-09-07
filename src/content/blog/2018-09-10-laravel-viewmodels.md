A view model is an abstraction used to simplify controller and model code.
View models are responsible for providing data for a view, 
which would otherwise come directly from the controller or the model.
They allow a better separation of concerns, and provide more flexibility for the developer.

In essence, view models are simple classes that can take a given set of data, 
and transform that data into something usable for the view.
In this post I'll show you some of the basic principles of the pattern, 
we'll take a look at how they integrate in Laravel projects,
and finally I'll show you how we use it in one of our Spatie projects.

So let's get started. 
Say you have a form to create a blog post for a given category.
You will need a way to fill the select box in your view with category options. 
The controller has to provide those.

```php
public function create()
{
    return view('blog.form', [
        'categories' => Category::all(),
    ]);
}
```
 
The above example works for the create method, 
but let's not forget we should also be able to edit existing posts.

```php
public function edit(Post $post)
{
    return view('blog.form', [
        'post' => $post,
        'categories' => Category::all(),
    ]);
}
```

Suddenly there's a new business requirement: 
users should be restricted in which categories they are allowed to post in.
This means the category selection should be restricted based on the user.

```php
return view('blog.form', [
    'categories' => Category::allowedForUser(
        current_user()
    )->get(),
]);
```

This approach doesn't scale. 
You'll have to change code both in the `create` and `edit` method.
Can you imagine what happens when you need to add tags to a post?
Or if there's a special admin form which also needs these categories and tags?

This is where view models come into play. 
They encapsulate this logic so that it can be reused in different places.

```php
class PostFormViewModel
{
    public function __construct(
        User $user, 
        Post $post = null
    ) {
        $this->user = $user;
        $this->post = $post;
    }
    
    public function post(): Post
    {
        return $this->post ?? new Post();
    }
    
    public function categories(): Collection
    {
        return Category::allowedForUser($this->user)->get();
    }
}
```

Let's name a few key features of such a class:

- All dependencies are injected, this gives the most flexibility to the outside.
- The view model exposes some methods that can be used by the view.
- There will always be a post provided to support both creating and editing in the form.

This is how our controller now looks:

```php
class PostsController
{
    public function create()
    {
        $viewModel = new PostFormViewModel(
            current_user()
        );
        
        return view('blog.form', compact('viewModel'));
    }
    
    public function edit(Post $post)
    {
        $viewModel = new PostFormViewModel(
            current_user(), 
            $post
        );
    
        return view('blog.form', compact('viewModel'));
    }
}
```

These are the two benefits of using view models: 

- They encapsulate the logic
- They can be reused in multiple contexts

## View models in Laravel

The previous example showed a simple class with some methods. 
Within a Laravel project, there are a few more possibilities though.

You can pass a view model directly to the `view` function if the view model implements `Arrayable`. 

```php
public function create()
{
    $viewModel = new PostFormViewModel(
        current_user()
    );
    
    return view('blog.form', $viewModel);
}
```

The view can now directly use the view model's properties like `$post` and `$categories`.

You can also return the view model itself, by implementing `Responsable`. 
This can be useful when you're saving the form via an AJAX call, 
and want to repopulate it with up-to-date data after the call is done. 

```php
public function update(Request $request, Post $post)
{
    // Update the post…

    return new PostFormViewModel(
        current_user(),
        $post
    );
}
```

You might see a similarity between view models and Laravel resources.
Remember that resources map one-to-one on a model, when view models may provide any data to the view they want.

In one of our projects, we're actually using resources in view models!

```php
class PostViewModel
{
    // …
    
    public function values(): array
    {
        return PostResource::make(
            $this->post ?? new Post()
        )->resolve();
    }
}
```

Finally, in this project we're working with Vue form components, which require JSON data.
We've made an abstraction which provides this JSON data instead of objects or arrays, 
when calling the magic getter:

```php
abstract class ViewModel
{
    // …
    
    public function __get($name): ?string
    {
        $name = Str::camel($name);
    
        // Some validation…
    
        $values = $this->{$name}();
    
        if (! is_string($values)) {
            return json_encode($values);
        }
    
        return $values;
    }
}
```

Now, instead of calling the view model methods, we can call their property and get a JSON back.

```html
<select-field
    label="{{ __('Post category') }}"
    name="post_category_id"
    :options="{{ $postViewModel->post_categories }}"
></select-field>
```

---

In summary, view models can be a viable alternative to working with the data directely in a controller.
They allow for better reusability and encapsulate logic that doesn't belong in the controller. 

You're also not confined to using them with forms. 
For example: at Spatie we also use them to populate facet filter options, 
based on a complex context the user is currently working in.

I'd recommend you trying this pattern out. 
You don't need anything to get started by the way. 
All Laravel gimmicks listed above are optional and can be added depending on your use case.

But just in case you'd like to use Laravel gimmicks, we've got a package for it 🤗: 
[spatie/laravel-viewmodel](*https://github.com/spatie/laravel-viewmodel).

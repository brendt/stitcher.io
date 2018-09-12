View models are an abstraction to simplify controller and model code.
View models are responsible for providing data to a view, 
which would otherwise come directly from the controller or the model.
They allow a better separation of concerns, and provide more flexibility for the developer.

In essence, view models are simple classes that take some data, 
and transform it into something usable for the view.
In this post I'll show you the basic principles of the pattern, 
we'll take a look at how they integrate in Laravel projects,
and finally I'll show you how we use the pattern in one of [Spatie](*https://spatie.be)'s, our company, projects.

Let's get started. 
Say you have a form to create a blog post with a category.
You'll need a way to fill the select box in the view with category options. 
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

Next there's a new business requirement: 
users should be restricted in which categories they are allowed to post in.
In other words: the category selection should be restricted based on the user.

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
Or if there's another special admin form for creating and editing posts?

The next solution is to have the post model itself provide the categories, like so:

```php
class Post extends Model
{
    public static function allowedCategories(): Collection 
    {
        return Category::query()
            ->allowedForUser(current_user())
            ->get();
    }
}
```

There are numerous reasons why this is a bad idea, though it happens often in Laravel projects.
Let's focus on the most relevant problem for our case: it still allows for duplication.

Say there's a new model `News` which also needs the same category selection.
This causes again duplication, but on the model level instead of in the controllers.

Another option is to put the method on the `User` model.
This makes the most sense, but also makes maintenance harder.
Imagine we're using tags as mentioned before. 
They don't rely on the user. 
Now we need to get the categories from the user model, and tags from somewhere else.

I hope it's clear that using models as data providers for views also isn't the golden bullet.

In summary, wherever you try to get the categories from, 
there always seems to be some code duplication.
This makes it harder to maintain and reason about the code.

This is where view models come into play. 
They encapsulate all this logic so that it can be reused in different places.
They have one responsibility and one responsibility only: providing the view with the correct data.

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
- There will either be a new or existing post provided by the `post` method, 
depending on whether your creating or editing a post.

This is what the controller looks like:

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

And finally the view can use it like so:

```html
<input value="{{ $viewModel->post()->title }}" />
<input value="{{ $viewModel->post()->body }}" />

<select>
    @foreach ($viewModel->categories() as $category)
        <option value="{{ $category->id }}">
            {{ $category->name }}
        </option>
    @endforeach
</select>
``` 

These are the two benefits of using view models: 

- They encapsulate the logic
- They can be reused in multiple contexts

## View models in Laravel

The previous example showed a simple class with some methods.
This is enough to use the pattern,
but within Laravel projects, there are a few more niceties we can add.

For example, you can pass a view model directly to the `view` function if the view model implements `Arrayable`. 

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
The previous example now looks like this:

```html
<input value="{{ $post->title }}" />
<input value="{{ $post->body }}" />

<select>
    @foreach ($categories as $category)
        <option value="{{ $category->id }}">
            {{ $category->name }}
        </option>
    @endforeach
</select>
``` 

You can also return the view model itself as JSON data, by implementing `Responsable`. 
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
Remember that resources map one-to-one on a model, when view models may provide whatever data they want.

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

Instead of calling the view model methods, we can call their property and get a JSON back.

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

You're also not confined to forms when using them. 
At Spatie we also use them to populate facet filter options, 
based on a complex context the user is currently working in.

I'd recommend trying this pattern out. 
You don't need anything to get started by the way. 
All Laravel gimmicks listed above are optional and can be added depending on your use case.

And just in case you'd like to use Laravel gimmicks, we've got a package for it: 
[spatie/laravel-view-models](*https://github.com/spatie/laravel-view-models) 🤗.

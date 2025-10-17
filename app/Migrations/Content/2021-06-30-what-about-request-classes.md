In some of our larger Laravel projects, we prefer to map request data to data transfer objects. By doing so we gain static analysis insights in what kind of data we're actually dealing with.

Such a request/dto setup usually looks something like this. Here's the request class handling validation of raw incoming data:

```php
class PostEditRequest extends Request
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'unique:posts,title', 'max:255'],
            'status' => ['required', 'string'],
            'body' => ['required', 'string'],
            'date' => ['required', 'date_format:Y-m-d'],
            'author_id' => ['nullable', 'exists:authors,id'],
            'tags' => [new <hljs type>CollectionRule</hljs>(<hljs type>Tag</hljs>::class)],
        ];
    }
}
```

And here's the DTO that represents that data in a way so that PHP, our IDE and external static analysers can understand it (note that I'm using our [data-transfer-object](*https://github.com/spatie/data-transfer-object) package here):

```php
class PostEditData extends DataTransferObject
{
    public <hljs type>string</hljs> <hljs prop>$title</hljs>;
    
    public <hljs type>PostStatus</hljs> <hljs prop>$status</hljs>;
    
    public <hljs type>string</hljs> <hljs prop>$body</hljs>;
    
    public <hljs type>Carbon</hljs> <hljs prop>$date</hljs>;
    
    public <hljs type>?string</hljs> <hljs prop>$authorId</hljs>;
    
    #[<hljs type>CastWith</hljs>(<hljs type>ArrayCaster</hljs><hljs text>::class</hljs>, <hljs prop>itemType</hljs>: <hljs type>Tag</hljs><hljs text>::class</hljs>)]
    public <hljs type>array</hljs> <hljs prop>$tags</hljs>;
}
```

Finally, there's the controller in between that converts the validated request data to a DTO and passes it to an action class to be used in our business processes:

```php
class PostEditController
{
    public function __invoke(
        <hljs type>UpdatePostAction</hljs> $updatePost,
        <hljs type>Post</hljs> $post, 
        <hljs type>PostEditRequest</hljs> $request,
    ) {
        return $updatePost(
            <hljs prop>post</hljs>: $post,
            <hljs prop>data</hljs>: new <hljs type>PostEditData</hljs>(...$request-><hljs prop>validated</hljs>()), 
        );
    }
}
```

I've been thinking about the overhead that's associated with this two-step request/dto transformation. In the end, we only really care about a valid, typed representation of the data that's sent to our server, we don't really care about working with an array of raw request data.

{{ cta:mail }}

So why not do exactly that: have a way for our request classes to be properly typed, without the overhead of having to transform it manually to a DTO? 

I could build up some suspense here to get you all excited about it, but I trust my readers to be able to draw their own, informed conclusions, so I'm just going to show you what it would look like in the end:

```php
class PostEditRequest extends Request
{
    #[<hljs type>Rule</hljs>(<hljs type>UniquePostRule</hljs><hljs text>::</hljs><hljs keyword>class</hljs>)]
    #[<hljs type>Max</hljs>(<hljs text>255</hljs>)]
    public <hljs type>string</hljs> <hljs prop>$title</hljs>;
    
    public <hljs type>PostStatus</hljs> <hljs prop>$status</hljs>;
    
    public <hljs type>string</hljs> <hljs prop>$body</hljs>;
    
    #[<hljs type>Date</hljs>(<hljs text>'Y-m-d'</hljs>)]
    public <hljs type>Carbon</hljs> <hljs prop>$date</hljs>;
    
    public <hljs type>?string</hljs> <hljs prop>$authorId</hljs>;
    
    #[<hljs type>Rule</hljs>(<hljs type>CollectionRule</hljs><hljs text>::</hljs><hljs text>class</hljs>, <hljs prop>type</hljs>: <hljs type>Tag</hljs><hljs text>::</hljs><hljs text>class</hljs>)]
    public <hljs type>array</hljs> <hljs prop>$tags</hljs>;
}
```

Some people might say we're combining two responsibilities in one class: validation and data representation. They are right, but I'd say the old approach wasn't any different. Take a look again at the `rules` method in our old request:

```php
class PostEditRequest extends Request
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'unique:posts,title', 'max:255'],
            'status' => ['required', 'string'],
            'body' => ['required', 'string'],
            'date' => ['required', 'date_format:Y-m-d'],
            'author_id' => ['nullable', 'exists:authors,id'],
            'tags' => [new <hljs type>CollectionRule</hljs>(<hljs type>Tag</hljs>::class)],
        ];
    }
}
```

We're also validating type information here, it's just more hidden and can't be interpreted by an IDE or other static analyser. The only thing I suggest we do different is to properly use PHP's built-in type system to its full extent, and fill the gaps for more complex validation rules with [attributes](*/blog/attributes-in-php-8).

Finally, our controller could be refactored like so:

```php
class PostEditController
{
    public function __invoke(
        <hljs type>UpdatePostAction</hljs> $updatePost,
        <hljs type>Post</hljs> $post, 
        <hljs type>PostEditRequest</hljs> $data,
    ) {
        return $updatePost(
            <hljs prop>post</hljs>: $post,<hljs red full><hljs prop>            data</hljs>: new <hljs type>PostEditData</hljs>(...$request-><hljs prop>validated</hljs>()),</hljs><hljs green full><hljs prop>            data</hljs>: $data,</hljs> 
        );
    }
}
```

I didn't just come up with this idea by the way, there are a number of modern web frameworks doing exactly this:

- [Rocket in Rust](*https://api.rocket.rs/master/rocket/form/validate/index.html)
- [ASP.NET MVC](*https://docs.microsoft.com/en-us/previous-versions/aspnet/hh882339(v=vs.110))
- [Actix in Rust](*https://docs.rs/actix-web-validator/2.1.1/actix_web_validator/)

And, finally: I don't think implementing this in Laravel would be all that difficult. We could even create a standalone package for it. All we need to do is build the request rules dynamically based on the public properties of the request, and fill them whenever a request comes in. I reckon the biggest portion of work is in creating the attributes to support all of Laravel's validation rules.

Anyway, I'm just throwing the idea out there to see what people think of it. Feel free to share your thoughts on [Twitter](*https://twitter.com/brendt_gd/status/1409808574860214276) with me.

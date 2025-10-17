---
title: 'Abstract resources in Laravel Nova'
next: improvements-on-laravel-nova
meta:
    description: 'Enrich your resources in Laravel Nova'
footnotes:
    - { link: 'https://www.masteringnova.com', title: 'Mastering Nova', description: ' — An in-depth course on Laravel Nova by Bruno Falcao' }
    - { link: /blog/laravel-beyond-crud, title: 'Laravel beyond CRUD', description: ' — A series about managing larger than average Laravel applications' }
    - { link: /blog/php-8-in-8-code-blocks, title: 'PHP 8 in 8 code blocks', description: ' — The best features of PHP 8' }
author:
    name: 'Bruno Falcao'
    url: 'https://twitter.com/brunocfalcao'
---

One of the major coding architecture strategies I use when building a complex Laravel Nova project
is the ability to have an abstract resource class.

*Before starting by the way, if you want deep dive in Nova, I suggest you subscribe to updates
in my upcoming course [Mastering Nova](*https://www.masteringnova.com) that will be released
this mid-summer!*

---

An abstract resource class will inherit the base `Resource` class. This allows you to
override specific methods to add functionality on your real resource classes.

In the end, any method that you improve in your custom base class, will be available on your model resources.
I'll show you how to create the abstract resource, and then we'll at concrete improvements.

We start by creating a file AbstractResource.php inside `app/Nova`, like this:

```
app/
  Nova/
     AbstractResource.php
```

At first, the `AbstractResource` looks like this:

```php
namespace App\Nova;

abstract class AbstractResource extends Resource
{
}
```

Next, in your Resource classes just inherit from this abstract Resource instead
of the Nova Resource one:

``` php
namespace App\Nova;

use App\Nova\AbstractResource;

class Review extends AbstractResource
{
    //
}
```

---

So, let's look at some examples of improvements you can add to your new
abstract resource.

### Default sorting

On your abstract Resource write this code:

```php
public static function indexQuery(<hljs type>NovaRequest</hljs> $request, $query)
{
    $uriKey = static::<hljs prop>uriKey</hljs>();
    
    if (($request-><hljs prop>orderByDirection</hljs> ?? null) !== null) {
        return $query;
    }
    
    if (! empty(static::<hljs prop>$indexDefaultOrder</hljs>)) {
        $query-><hljs prop>getQuery</hljs>()-><hljs prop>orders</hljs> = [];

        return $query-><hljs prop>orderBy</hljs>(
            <hljs prop>key</hljs>(static::<hljs prop>$indexDefaultOrder</hljs>), 
            <hljs prop>reset</hljs>(static::<hljs prop>$indexDefaultOrder</hljs>)
        );
    }
}
```

Then on your model resource:

```php
public static <hljs prop>$indexDefaultOrder</hljs> = ['email' => 'asc'];
```

This will sort your index query by "email, asc" in case there is not a pre-selected
sorting order.

### Search relationships

If you have a relationship field, you might have seen that you cannot use it
to search on your Resource search field. In that case, you can use the
`titasgailius/search-relations` [package](*https://github.com/TitasGailius/nova-search-relations).

To install it, just import it via Composer:

```
composer require titasgailius/search-relations
```

Then in your Abstract Resource, you can add it like:

```php
use Titasgailius\SearchRelations\SearchesRelations;

abstract class AbstractResource extends Resource
{
    use <hljs type>SearchesRelations</hljs>;
}
```

Henceforth, on your model resources, you can simply add:

```php
public static <hljs prop>$searchRelations</hljs> = [
    'user' => ['username', 'email'],
];
```

where the key is your relationship name, and then an array of searchable column values. Therefore you can now search on your relationship columns!

### Sharing Cards, Lenses, Actions and Filters

Let's say you would like to have a generic card that shows information about when
was the last time your current resource was updated, and some other extra information
regarding your resource; or an action that actually will change status on models
that share a `status_type` column. 

All of this functionality can be shared between model resources. 

As an example, let's say you want to add a new Card to all of the model resources that share your abstract resource, you can do it like:

```php
public function cards(<hljs type>Request</hljs> $request){
    return [
        new <hljs type>ResourceInformation</hljs>(
            $this-><hljs prop>getCurrentResourceInstance</hljs>($this-><hljs prop>getModelInstance</hljs>())
        ),
    ];
}

protected function getModelInstance()
{
    $resourceKey = <hljs prop>explode</hljs>('/', <hljs prop>request</hljs>()-><hljs prop>path</hljs>())[1];

    $resourceClass = <hljs type>Nova</hljs>::<hljs prop>resourceForKey</hljs>($resourceKey);

    return new $resourceClass::$model;
}
```

and in your model Resource:

```php
public function cards(<hljs type>Request</hljs> $request)
{
    return <hljs prop>array_merge</hljs>(
        [/* your cards */], 
        parent::<hljs prop>cards</hljs>($request),
    );
}
```

### Disable 'trashed' behavior

The `BelongsTo` field already has an option to remove the checkbox 'With Trashed'
(basically not to show trashed items), but what if want to remove it from any
other relationship operation (e.g.: `BelongsToMany`)?

You just need to apply the following code in your abstract resource:

```php
use Illuminate\Support\Facades\Gate;

/**
 * Based the trashed behavior on a new policy called trashedAny()
 *
 * @return boolean
 */
public static function softDeletes()
{
    // Is this resource authorized on trashedAny?
    if (static::<hljs prop>authorizable</hljs>()) {
        if (! <hljs prop>method_exists</hljs>(
            <hljs type>Gate</hljs>::<hljs prop>getPolicyFor</hljs>(static::<hljs prop>newModel</hljs>()),
            'trashedAny'
        )) {
            return true;
        }       

        return <hljs type>Gate</hljs>::<hljs prop>check</hljs>('trashedAny', static::class));
    };

    return parent::<hljs prop>softDeletes</hljs>();
}
```

in this example, all you have to do is to define a policy for your model, and then
create a new method called `trashedAny(User $user)`, as example:

```php
public function trashedAny(<hljs type>User</hljs> $user)
{
    return false;
}
```

---

These were examples that can trigger your thoughts about how to leverage
Abstract Resources on your Nova projects.

*And if I was able to convince you :) I suggest you subscribe to updates
in my upcoming course [Mastering Nova](*https://www.masteringnova.com) that will be released
this mid-summer!*

Best,
Bruno

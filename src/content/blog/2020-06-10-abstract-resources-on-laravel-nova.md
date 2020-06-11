One of the major coding architecture strategy I use when doing a complex Laravel Nova project
is the ability to have an abstract Resource class.

*Before starting by the way, if you want deep dive in Nova, I suggest you subscribe to updates
in my upcoming course [Mastering Nova](*https://www.masteringnova.com) that will be released
this mid-summer!*

An abstract Resource class is a resource that you create that just have
an abstract class that inherits from the Resource class. This will allow you to
override specific methods that will leverage its methods to be then used
on your real model Resources.

In the end, any method that you improve, it will be improved on your model Resources.
I'll show you how to create the abstract Resource, and then see how examples
of improvements, you can code there so all of your model Resources benefit from it.

1. Create a file AbstractResource.php inside your app/Nova, like this:

```
   app/
      Nova/
         AbstractResource.php
```

Then add this code on your AbstractResource.php:

```
<?php

namespace App\Nova;

abstract class AbstractResource extends Resource
{

}
```

And then, on your Resource classes just inherit from this abstract Resource instead
of the Nova Resource one:

```
<?php

namespace App\Nova;

use App\Nova\AbstractResource;

class Review extends AbstractResource
{
    //
}
```

So, let me show you some examples of improvements you can add to your new
abstract Resource so all of your model Resources will inherit.

### Default sorting orders in case no sorting order was selected on your
index view

On your abstract Resource write this code:

```
    public static function indexQuery(NovaRequest $request, $query)
    {
        $uriKey = static::uriKey();

        if (!is_null(optional($request)->orderByDirection)) {
            return $query;
        }

        if (!empty(static::$indexDefaultOrder)) {
            $query->getQuery()->orders = [];
            return $query->orderBy(key(static::$indexDefaultOrder), reset(static::$indexDefaultOrder));
        };
```

Then on your model Resource:

```
    [...]

    public static $indexDefaultOrder = ['email' => 'asc'];

    [...]
```

This will sort your indexQuery by "email, asc" in case there is not a pre-selected
sorting order.

### Ability to search in relationship columns into your Nova Resource search feature

If you have a relationship field you might have seen that you cannot use it
to search on your Resource search field. In that case, you can use the
`titasgailius/search-relations` [package](*https://github.com/TitasGailius/nova-search-relations).

To install it, just import it via Composer:

```
    composer require titasgailius/search-relations
```

Then in your Abstract Resource, you can add it like:

```
use Titasgailius\SearchRelations\SearchesRelations;
[...]

abstract class AbstractResource extends Resource
{
    use SearchesRelations;

    [...]
}
```

Henceforth, on your model Resources that inherit from our Abstract Resource,
you just need to add:

```
    public static $searchRelations = [
        'user' => ['username', 'email'],
    ];
```

where the key is your relationship name, and then an array of searchable column values. Therefore you can search on your relationship columns
and since you're using an abstract Resource it can be also used on all of your
model Resources!

### Sharing Cards, Lenses, Actions or Filters across model Resources

Let's say you would like to have a generic card that shows information about when
was the last time your current Resource was updated, and some other extra information
regarding your resource. Or an action that actually will change status on models
that share a `status_type` column. All of these can be shared between model Resources
by the fact that you're overriding the methods on them. As an example, let's say
you want to add a new Card to all of the model Resources that share your
Abstract Resource, you can do it like:

```
    AbstractResource.php

    [...]

    public method cards(Request $request){
        return [new ResourceInformation($this->getCurrentResourceInstance($this->getModelInstance()))]
    }

    protected method getModelInstance(){
        $resourceKey = explode('/', request()->path())[1];
        $resourceClass = Nova::resourceForKey($resourceKey);
        return $resourceClass::$model->all()->count();
    }
```
and in your model Resource:

```
    ReviewResource.php

    [...]

    public function cards(Request $request)
        return array_merge([<your cards>], parent::cards($request));
    }
```

These were examples that can trigger your thoughts about how to leverage
Abstract Resources on your Nova projects.

*And if I was able to convince you :) I suggest you subscribe to updates
in my upcoming course [Mastering Nova](*https://www.masteringnova.com) that will be released
this mid-summer!*

Best,
Bruno
## ... a long time ago in a galaxy far, far away ...

[Laravel Nova](https://nova.laravel.com)] was launched on 22nd August 2018, as
the official administration panel for Laravel web applications.

<div class="image-noborder"></div>

![](/resources/img/blog/6-essential-things-you-need-to-know-about-nova/nova-1.jpg)

That was [Taylor Otwell](https://twitter.com/taylorotwell) making the presentation at
the Laracon US 2018, and since then Nova has evolved a lot, offering a better a UI experience,
faster performance, and at the end giving backend creators a true experience on how
to have a seamless integration between a Laravel application and the respective Resources
(or Eloquent *empowered* models) integrated into a nice CRUD interface.

When Nova was created, a lot of expectations were already being created by the Laravel community
in the way that Laravel Nova should be a real administration back-office system, since
this was a niche that was already covered by very nice alternative CRUD platforms like
[QuickAdminPanel](https://2019.quickadminpanel.com/) or [Laravel BackPack](https://backpackforlaravel.com/)
but they weren't an official Laravel product. So, when Nova was launched right after Laracon
it went like a sales hit, everybody was talking about it, and everybody that bought a license
experienced challenges to using it too. But those are things from the past :)

Nova evolved since then and evolved a lot! It's no longer a simple Resource Management tool
and I want to share with you 6 must-have features that might help you when you are developing
your Nova projects. Also, if you want deep dive in Nova, I suggest you subscribe to updates
in my upcoming course [Mastering Nova](https://www.masteringnova.com) that will be released
this mid-summer!

## 1. Creating your Custom CSS Theme

The first versions of Nova you weren't able to customize your Theme and to tweak it
in a way without having your CSS code being overridden each time a new version of Nova was
being published.

Now you can create your CSS theme, like this:

```
    php artisan nova:theme brunocfalcao/masteringnova-theme
```

After that, you have a CSS class in your new package at `resources/css/theme.css` where
you can then apply all the new [Tailwind](https://tailwindcss.com) classes that you want
to use in your Nova instance.

If you want to even fully customize the entire Nova classes you can enable it using a
custom package, then use the Nova::enableThemingClasses() to fully brand it to your needs.

This feature will prefix the Vue components with the string `nova-`.
For example, Nova will
add the class name nova-heading to the top-level of the Heading component so you can
then style it from there.

```
// NovaServiceProvider.php
public function boot()
{
    Nova::enableThemingClasses()
}

// app.js
/**
 * If configured, register a global mixin to add theming-friendly CSS
 * classnames to Nova's built-in Vue components. This allows the user
 * to fully customize Nova's theme to their project's branding.
 */
if (window.config.themingClasses) {
  Vue.mixin(ThemingClasses)
}
```

## 2. Dynamic Field visibility statuses

in the version 1.x of Nova you would control your Fields visibility using 8 methods:

```
    hideFromIndex()
    hideFromDetail()
    hideWhenCreating()
    hideWhenUpdating()
    onlyOnIndex()
    onlyOnDetail()
    onlyOnForms()
    exceptOnForms()
```

Now, you have the `show*()` methods that allow you to show your Resource in the
respective display context without the dependency of other display contexts. For instance
you can have a showOnIndex() and a showOnCreating(), using a callback on the method
that should return `true`.

```
    showOnIndex()
    showOnDetail()
    showOnCreating()
    showOnUpdating()
```

## 3. New Field types to create a better User experience

Since version 1.x that we see being added new field types to Nova. Let me highlight
you some of the ones I consider the best additions:

### SparkLine Field

See it a Chart "on-the-fly" directly in your Resource index or detail contexts.

![](/resources/img/blog/6-essential-things-you-need-to-know-about-nova/sparkline-field.jpg)

```
    Sparkline::make('Total devices Per Week')->data($data)
             ->asBarChart()
             ->width(300),
```

### Key-Value Field

Key-value fields are ways for you to interact with JSON data type columns, providing
a way to manage Key-Value entries in a CRUD way.

![](/resources/img/blog/6-essential-things-you-need-to-know-about-nova/key-value-field.jpg)

```
    KeyValue::make('Server Data', 'server_data')
            ->keyLabel('Parameter')
            ->valueLabel('Value')
            ->actionText('Add Server Parameter')
            ->rules('json')
            ->nullable(),

    In your model:
    protected $casts = [
        'server_data' => 'json'
    ];
```

### Hidden Field

At first, it might not be useful, but believe me, it's great to have it since
you can apply data computations to be sent to your UI components.

```
    Hidden::make('User', 'user_id')->default(function ($request) {
        return $request->user()->id;
    });
```

### VaporFile and VaporImage Fields

These are the *last kids on the block* since they allow you to upload files or
images into your Laravel Vapor instance. They will generate a temporary upload
URL for Amazon S3 and will immediately upload the file.

![](/resources/img/blog/6-essential-things-you-need-to-know-about-nova/vapor-fields.jpg)

```
    VaporFile::make('Filename'),
    VaporImage::make('Avatar')->maxWidth(80)->rounded(false),
```

### Searchable Select Fields

On the last Nova version 3.6.0 you can now have a Searchable Select field.

![](/resources/img/blog/6-essential-things-you-need-to-know-about-nova/select-searchable.jpg)

```
    Select::make('Tags', 'tag_id')
        ->searchable()
        ->options(\App\Tag::all()->pluck('name', 'id'))
        ->displayUsingLabels(),
```

## 4. You can change the Stubs

Since version 3.3.0 it's possible to publish the Nova stubs so you can change them to your
own needs.

```
    php artisan nova:stubs [--force]
```

The stubs are published directly in your app folder in a directory called "stubs".

## 5. Ability to sort your Resources by priority on the Sidebar

This one I think it's undocumented but you can sort your Resources given a specific attribute in your Resource.

```
    [In your NovaServiceProvider.php]

    Nova::sortResourcesBy(function ($resource) {
        return $resource::$priority ?? 9999;
    });

    [ In your Resource ]

    public static $priority = 10; // Or any other number.
```

The Sidebar Resources will then be sorted by this priority. Neat!

## 6. Customize where a Global Search link can take you to

In specific cases, you might want to have your Global Search targeted Resource
to go to Edit and not to Detail, or vice-versa. All you have to do is to add
this static property on your Resource:

```
    public static $globalSearchLink = 'detail';
```

Hope you enjoyed, and in case you want to continue learning Laravel Nova you can
pre-subscribe my [Mastering Nova Course](https://www.masteringnova.com) anytime!

Bruno Falcao
[@brunocfalcao](https://twitter.com/brunocfalcao)
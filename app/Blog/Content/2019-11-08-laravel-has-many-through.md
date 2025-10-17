---
title: "Laravel's HasManyThrough cheatsheet"
meta:
    description: "How to use Laravel's HasManyTrough"
footnotes:
    - { link: /blog/laravel-custom-relation-classes, title: 'Building custom relation classes in Laravel' }
---

```txt
- The current model <hljs red>Country</hljs> has a relation to <hljs yellow>Post</hljs> via <hljs blue>User</hljs>
- The <hljs blue>intermediate model</hljs> is linked to the <hljs red>current model</hljs> via <hljs blue>users.country_id</hljs>
- The <hljs yellow>target model</hljs> is linked to the <hljs blue>intermediate model</hljs> via <hljs yellow>posts.user_id</hljs>
- <hljs blue>users.country_id</hljs> maps to <hljs red>countries.id</hljs>
- <hljs yellow>posts.user_id</hljs> maps to <hljs blue>users.id</hljs>
```

```txt
<hljs red>countries</hljs>
    <hljs red>id</hljs> - integer
    name - string

<hljs blue>users</hljs>
    <hljs blue>id</hljs> - integer
    <hljs blue>country_id</hljs> - integer
    name - string

<hljs yellow>posts</hljs>
    id - integer
    <hljs yellow>user_id</hljs> - integer
    title - string
```

```php
class <hljs red>Country</hljs> extends Model
{
    public function <hljs yellow>posts</hljs>()
    {
        return $this-><hljs prop>hasManyThrough</hljs>(
            '<hljs yellow>App\Post</hljs>',
            '<hljs blue>App\User</hljs>',
            '<hljs blue>country_id</hljs>', // Foreign key on users table...
            '<hljs yellow>user_id</hljs>', // Foreign key on posts table...
            '<hljs red>id</hljs>', // Local key on countries table...
            '<hljs blue>id</hljs>' // Local key on users table...
        );
    }
}
```

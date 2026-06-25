---
title: "Laravel's HasManyThrough cheatsheet"
meta:
    description: "How to use Laravel's HasManyTrough"
footnotes:
    - { link: /blog/laravel-custom-relation-classes, title: 'Building custom relation classes in Laravel' }
---

```txt
- The current model Country has a relation to Post via User
- The intermediate model is linked to the current model via users.country_id
- The target model is linked to the intermediate model via posts.user_id
- users.country_id maps to countries.id
- posts.user_id maps to users.id
```

```txt
countries
    id - integer
    name - string

users
    id - integer
    country_id - integer
    name - string

posts
    id - integer
    user_id - integer
    title - string
```

```php
class Country extends Model
{
    public function posts()
    {
        return $this->hasManyThrough(
            'App\Post',
            'App\User',
            'country_id', // Foreign key on users table...
            'user_id', // Foreign key on posts table...
            'id', // Local key on countries table...
            'id' // Local key on users table...
        );
    }
}
```

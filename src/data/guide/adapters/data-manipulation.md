Data manipulation adapters are used to modify the data set of a variable. You might want to order, limit or filter your
 entries. That can be done with these adapters.

### Order

Applies order to a data set, based on a field of that data.

```yaml
/blog:
    template: blog/overview
    data:
        posts: data/blog.yml
    adapters:
        order:
            posts:
                field: date
                direction: desc
```

### Limit

Limit a data set.

```yaml
/blog:
    template: blog/overview
    data:
        posts: data/blog.yml
    adapters:
        limit:
            posts: 2
```

### Filter

Filter a data set.

```yaml
/blog:
    template: blog/overview
    data:
        posts: data/blog.yml
    adapters:
        filter:
            posts:
                highlight: true
```


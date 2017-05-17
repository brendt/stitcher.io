The collection adapter takes a page with a collection of entries, and generates a detail page for each entry in the collection.

```yaml
/examples/{id}:
    template: examples/detail
    data:
        example: collection.yml
    adapters:
        collection:
            variable: example
            field: id
```

Furthermore, an extra variable called `browse` is added. This variable holds the information to browse through detail pages.
 The `browse` variable has two keys: `next` and `prev`.
 
```html
{if $browse.prev}
    <a class="prev" href="/guide/{$browse.prev.id}">
        Previous: {$browse.prev.title|strtolower}
    </a>
{/if}

{if $browse.next}
    <a class="next" href="/guide/{$browse.next.id}">
        Next: {$browse.next.title|strtolower}
    </a>
{/if}
```

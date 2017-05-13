The pagination adapter takes a page with a collection of entries and generates pagination for that collection. This 
 adapter will generate several pages instead of one, and adds the `$pagination` variable to the template.

```yaml
# site/site.yml

/examples:
    template: examples/overview
    data:
        collection: collection.yml
    adapters:
    pagination:
        variable: collection
        entriesPerPage: 4
```

In `examples/overview.tpl`, this `$pagination` variable is now available.

```html
{$pagination}

{$pagination.next.url}
{$pagination.next.index}
{$pagination.prev.url}
{$pagination.prev.index}
{$pagination.current}
{$pagination.pages}

{if $pagination.prev}
    <a href="{$pagination.prev.url}">Previous page</a>
{/if}

{for $page = 1 to $pagination.pages}
    <a href="/my-url/page-{$page}">
        {$page}
    </a>
{/for}

{if $pagination.next}
    <a href="{$pagination.next.url}">Next page</a>
{/if}
```


### YAML data

Collection of data.

```yaml
# src/data/collection.yml

entries:
    entry-a:
        title: Example Entry A
        intro: Lorem ipsum dolor sit amet
        body: entry-a.md
        image:
            src: img/blue.jpg
            alt: A Blue image
    entry-b:
        title: Example Entry B
        intro: This is the second entry
        body: entry-a.md
        image: img/orange.jpg
```

```yaml
# site.yml

variables:
    enries: collection.yml
```

A file for one entry.

```yaml
# src/data/entry-a.yml

entry-a:
    title: Example Entry A
    intro: Lorem ipsum dolor sit amet
    body: entry-a.md
    image:
        src: img/blue.jpg
        alt: A Blue image
```

```yaml
# site.yml

variables:
    entryA: entry-a.yml
```

### Markdown

```md
// src/data/guide/index.md

## Title

Lorem ipsum dolor sit amet.
```

```yaml
# site.yml

variables:
    body: guide/index.md
```

### JSON

```json
// src/data/collection.json

{
    "entries": {
        "entry-a": {
            "title": "A title",
            "body": "guide/helper-functions.md"
        }
    }
}
```

```yaml
# site.yml

variables:
    entries: /collection.json
```


```json
// src/data/entries/entry-a.json

{
    "entry-a": {
        "title": "A title",
        "body": "guide/helper-functions.md"
    }
}
```

```yaml
# site.yml

variables:
    entryA: /entries/entry-a.json
```

### Folders

```sh
data/
 └── folder/
      ├── entry-a.yml
      ├── entry-b.yml
      ├── entry-c.yml
      ├── entry-d.yml
      └── entry-e.yml
```

```yaml
# site.yml

variables:
    entries: folder/
```

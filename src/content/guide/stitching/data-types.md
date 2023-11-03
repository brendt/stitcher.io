Stitcher supports different types of data sets. When parsing a page configuration file, Stitcher will automatically
 detect whether a variable is a parseable entry. If so, Stitcher will try to parse that value. If something were to fail,
 the original value is kept.
 
Important to note is that some data type (like yaml and JSON) can both represent a single entry, or a collection of entries.
 To let Stitcher know about this, you should use the key `entries` as root when you're using a collection.

### Yaml data

Yaml is probably the most developer-friendly format. A yaml file can represent a collection of entries, or a single one.

```yaml
# src/data/collection.yml

entries:
    entry-a:
        title: Example Entry A
        intro: Lorem ipsum dolor sit amet
        body: data/entry-a.md
        image:
            src: data/img/blue.jpg
            alt: A Blue image
    entry-b:
        title: Example Entry B
        intro: This is the second entry
        body: data/entry-a.md
        image: data/img/orange.jpg
```

```yaml
# site.yml

variables:
    entries: data/collection.yml
```

A file for one entry:

```yaml
# src/data/entry-a.yml

entry-a:
    title: Example Entry A
    intro: Lorem ipsum dolor sit amet
    body: data/entry-a.md
    image:
        src: data/img/blue.jpg
        alt: A Blue image
```

```yaml
# site.yml

variables:
    entryA: data/entry-a.yml
```

### Images

Stitcher will automatically parse JPG and PNG images. An image can be defined in two ways:

```yaml
image: data/img/my.png

image:
    src: data/img/my.jpg
```

The second option allows you to add extra variables to your image, the first one will always generate the following structure:

```yaml
image: 
    src: /path/to/my.png
    srcset: <generated srcset>
    sizes: <at the moment not used>
```

### MarkDown

Stitcher will automatically parse MarkDown files.

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

JSON can be used the same way as the yaml format. Again both for collections and single entries. The JSON format might be
 useful when syncing data from an API via a plugin, or if you just prefer JSON above yaml. 

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

A single entry:

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

When specifying a folder (a path ending in a `/`), Stitcher will look for all files in that folder, and parse them into
 one collection. File could even be of different data types.
 
```sh
data/
   folder/
        entry-a.yml
        entry-b.yml
        entry-c.yml
        entry-d.yml
        entry-e.json
```

```yaml
# site.yml

variables:
    entries: folder/
```

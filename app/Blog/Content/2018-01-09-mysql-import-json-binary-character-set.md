---
type: 'piece of code'
title: 'MySQL import: JSON with binary character set'
teaserTitle: 'MySQL JSON import'
meta:
    description: 'How to fix "cannot create a JSON value from a string with CHARACTER SET ''binary''" MySQL import error'
---

If you see this error when importing MySQL files:

```
cannot create a JSON value from a string with CHARACTER SET 'binary'
```

You should find and replace parts of the import file with the following regex:

Find: `(X'[^,\)]*')`, and replace by: `CONVERT($1 using utf8mb4)`

Source: [StackOverflow](*https://stackoverflow.com/questions/38078119/mysql-5-7-12-import-cannot-create-a-json-value-from-a-string-with-character-set).

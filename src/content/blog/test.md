*As you might know, normal UUIDs are stored as `CHAR(36)` fields in the database. 
 This has an enormous performance cost, because MySQL is unable to properly index these records.
 Take a look at the following graph, plotting the execution.*
 

- Hello
- World
    - Storing them in this format, MySQL has a lot less trouble indexing this table. 
      This is the graph plotting a much faster result. 
    - Storing them in this format, MySQL has a lot less trouble indexing this table. 
      This is the graph plotting a much faster result. 
 
## Saving UUIDs as binary data

Instead of saving UUIDs as, it's possible to store their actual binary data in a field. 
Storing them in this format, MySQL has a lot less trouble indexing this table. 
This is the graph plotting a much faster result.

![parrot](/resources/img/blog/responsive/parrot.jpg)

Instead of saving UUIDs as, it's possible to store their actual binary data in a field. 
Storing them in this format, MySQL has a lot less trouble indexing this table. 
This is the graph plotting a much faster result.

## DocBlocks

DocBlocks is a good tool to clarify what code actually does. Furthermore, IDEs like PHPStorm rely on certain DocBlocks. They are needed to provide correct autocomplete functionality in some cases. A frequent example is "array of objects". Yet modern PHP offers a lot of possibilities to write self-documenting code. DocBlocks often state the obvious things, which are already known by reading the code. 

Take a look again at the example above. There are no DocBlocks there. I've actually removed all redundant DocBlocks from the Stitcher core. I only kept DocBlocks which provide IDE autocomplete functionality and real documentation. I also disabled the automatic DocBlock generation in PHPStorm. 

> A Blockquote: There are two requirements for this method to work though, A and B - [Brent](#)

PHPStorm can fold code by default (Settings > Editor > General > Code Folding). I was a bit hesitant to enable it by default, but I can assure you this is an amazing feature once you're used to it. It's also more convenient than the file structure navigator many IDEs and editors provide. This approach allows you to see the visual structure, color and indentation of the class. 

You'll probably want to learn the keybinds associated with folding too. On Mac with PHPStorm, these are the defaults: `⌘⇧+`, `⌘⇧-`, `⌘+` and `⌘-`. 

### Subtitle 1

```css
footer {
    font-family: $font-body;
    margin-bottom: 2rem;
    padding-top:1rem;
    padding-bottom:1rem;
}

footer > nav {
    @include wrapper;
    text-align: center;
}
```

![parrot](/resources/img/blog/responsive/parrot.jpg)

### Subtitle 2

Instead of saving UUIDs as, it's possible to store their actual binary data in a field. 
Storing them in this format, MySQL has a lot less trouble indexing this table. 
This is the graph plotting a much faster result. 
Instead of saving UUIDs as, it's possible to store their actual binary data in a field. 
Storing them in this format, MySQL has a lot less trouble indexing this table. 
This is the graph plotting a much faster result. 

```php
namespace Pageon\Http;

class Header
{
    private $name;

    private $content;

    public function __construct(string $name, ?string $content = null)
    {
        $this->name = $name;
        $this->content = $content;
    }

    public static function make(string $name, ?string $content = null): Header
    {
        return new self($name, $content);
    }

    public function __toString(): string
    {
        return "{$this->name}: {$this->content}";
    }
}
```

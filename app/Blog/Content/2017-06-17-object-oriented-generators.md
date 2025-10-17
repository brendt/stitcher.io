---
type: 'piece of code'
title: 'Object oriented generators'
teaser: 'Another way to approach generators, in an object oriented fashion.'
meta:
    title: 'Process forks - Piece of Code'
    description: "Create loop-able, array-like collections in PHP with type checking.\n"
---

The following code shows an object oriented way of implementing a well known generator function: to read lines from a large file. 

```php
class FileReader implements \Iterator 
{
    private $handle;
    private $current;

    public static function read(string $fileName) : FileReader {
        return new self($fileName);
    }

    public function __construct(string $fileName) {
        $this->handle = fopen($fileName, 'r');
        $this->next();
    }

    public function __destruct() {
        fclose($this->handle);
    }

    public function current() {
        return $this->current;
    }

    public function next() {
        $this->current = fgets($this->handle);
    }

    public function key() {
        return ftell($this->handle);
    }

    public function valid() {
        return !feof($this->handle);
    }

    public function rewind() {
        rewind($this->handle);
    }
}
```

Using the file reader.

```php
$lines = FileReader::read('path_to_large_file.txt');

foreach ($lines as $line) {
    echo $line;
}
```

A comparison to using generators and the `yield` keyword, based on the tests I ran:

- This approach takes the same amount of time to execute.
- It has the same memory footprint as a generator function.
- It has the benefit of easier re-usability (in my opinion).

In comparison to `file_get_contents`: reading the same file required of 15MB of memory, whilst 
this solution required only 2MB, because it only reads one line in memory at a time.

To round up, this is the generator solution using `yield`.

```php
function read($fileName) {
    $handle = fopen($fileName, 'r');

    while (!feof($handle)) {
        yield fgets($handle);
    }

    fclose($handle);
}

$lines = read('path_to_large_file');

foreach ($lines as $line) {
    echo $line;
}
```

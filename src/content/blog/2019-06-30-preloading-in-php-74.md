PHP 7.4 adds preloading support, a feature that could improve the performance of your code significantly. 

Here's the short list:

- In order to preload files, you need to write a custom ((PHP)) script
- This script is executed once on server startup
- All preloaded files are available in memory for all requests
- Changes made to the source file won't have any effect, until the server is restarted

Let's look at it in depth.

## Differences to opcache

While preloading is built on top op opcache, it's not exactly the same.
Opcache will take your PHP source files, compile it to "opcodes", and store those compiled files on disk.

You can think of "opcodes" as a low-level representation of your code, that can be easily interpreted at runtime.
So opcache skips the translation step between your source files and what the ((PHP)) interpreted actually needs at runtime. A huge win!

But there's more to be gained! Opcached files don't know about other files. If you've got a class `A` extending from class `B`, you'd still need to link them together at runtime. Furthermore, opcache performs checks to see whether the source files were modified, and will invalidate its caches based on that.

So this is where preloading comes into play: it will not only compile source files to opcodes, but also link related classes, traits and interfaces together. It will then keep this "compiled" blob of runnable code — that is: code usable by the ((PHP)) interpreter — in memory.

When a request arrives at your server, you can now have parts of your codebase already loaded in memory, available for use, without any overhead.

So, what "parts of your codebase" are we talking about?

## Preloading in practice

For preloading to work, the developer has to tell the server which files to load. This is done with a simple ((PHP)) script, so there's nothing to be afraid of.

The rules are simple: 

- You provide the preload script in you php.ini file
- Every ((PHP)) file that is included within this script will be preloaded if possible

Say you'd want to preload your whole framework, Laravel for example. Your script will have to loop over all ((PHP)) files in the `vendor/laravel` directory, include them and be done.

Simple enough, right? Here's what such a script could look like:

```php
require_once __DIR__ . '/vendor/autoload.php';

class Preloader
{
    private array $ignores = [];
    private array $paths;
    private array $fileMap;

    public function __construct(string ...$paths)
    {
        $this->paths = $paths;
        $classMap = require __DIR__ . '/vendor/composer/autoload_classmap.php';
        $this->fileMap = array_flip($classMap);
    }

    public function paths(string ...$paths): Preloader
    { /* … */ }

    public function ignore(string ...$names): Preloader
    { /* … */ }

    public function load(): void
    {
        foreach ($this->paths as $path) {
            $this->loadPath(rtrim($path, '/'));
        }
    }

    private function loadPath(string $path): void
    {
        if (is_dir($path)) {
            $this->loadDir($path);

            return;
        }

        $this->loadClass($path);
    }

    private function loadDir(string $path): void
    {
        $handle = opendir($path);

        while ($file = readdir($handle)) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            $this->loadPath("{$path}/{$file}");
        }

        closedir($handle);
    }

    private function loadClass(string $path): void
    {
        $class = $this->fileMap[$path] ?? null;

        if ($this->shouldIgnore($class)) {
            return;
        }

        require_once($path);
    }

    private function shouldIgnore(?string $name): bool
    {
        if ($name === null) {
            return true;
        }

        foreach ($this->ignores as $ignore) {
            if (strpos($name, $ignore) === 0) {
                return true;
            }
        }

        return false;
    }
}

(new Preloader())
    ->paths(__DIR__ . '/vendor/laravel')
    ->ignore(\Illuminate\Filesystem\Cache::class)
    ->load();
```

Don't be overwhelmed, it's not very complex to understand.

After a `Preload` class is constructed, it has an array of paths to load. Calling `load()` will loop over these paths, and load them one by one.

```php
public function load(): void
{
    foreach ($this->paths as $path) {
        $this->loadPath(rtrim($path, '/'));
    }
}
```

In case we're dealing with a directory, we need to load every single file in it; otherwise we'll go ahead and try to load the class.

```php
private function loadPath(string $path): void
{
    if (is_dir($path)) {
        $this->loadDir($path);

        return;
    }

    $this->loadClass($path);
}
```

Loading a directory is as simple as looping over all its items, and calling `loadPath` on each individual one.

```php
private function loadDir(string $path): void
{
    $handle = opendir($path);

    while ($file = readdir($handle)) {
        if (in_array($file, ['.', '..'])) {
            continue;
        }

        $this->loadPath("{$path}/{$file}");
    }

    closedir($handle);
}
```

You might have noticed this script uses the composer's classmap? There's no need for our script to try and verify whether a file actually contains a valid class, if composer already knows about it. That's why we verify that the file we want to load is also known by composer as a class.

If the class is known to composer, we'll go ahead and require it.

```php
private function loadClass(string $path): void
{
    $class = $this->fileMap[$path] ?? null;

    if ($this->shouldIgnore($class)) {
        return;
    }

    require_once($path);
}
```

Obviously, things are still missing in this script. ((PHP)) files that don't contain classes could also be preloaded, but our script ignores them for now.

## Server restarts

## Unlinked classes

## Composer support

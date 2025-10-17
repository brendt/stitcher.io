With PHP 7.4, support for preloading was added, a feature that could improve the performance of your code significantly. 

In a nutshell, this is how it works:

- In order to preload files, you need to write a custom PHP script
- This script is executed once on server startup
- All preloaded files are available in memory for all requests
- Changes made to preloaded files won't have any effect, until the server is restarted

Let's look at it in depth.

## Opcache, but more

While preloading is built on top of opcache, it's not exactly the same.
Opcache will take your PHP source files, compile it to "opcodes", and store those compiled files on disk.

You can think of opcodes as a low-level representation of your code, that can be easily interpreted at runtime.
So opcache skips the translation step between your source files and what the PHP interpreter actually needs at runtime. A huge win!

But there's more to be gained. Opcached files don't know about other files. If you've got a class `A` extending from class `B`, you'd still need to link them together at runtime. Furthermore, opcache performs checks to see whether the source files were modified, and will invalidate its caches based on that.

So this is where preloading comes into play: it will not only compile source files to opcodes, but also link related classes, traits and interfaces together. It will then keep this "compiled" blob of runnable code — that is: code usable by the PHP interpreter — in memory.

When a request arrives at the server, it can now use parts of the codebase that were already loaded in memory, without any overhead.

So, what "parts of the codebase" are we talking about?

## Preloading in practice

For preloading to work, you — developers — have to tell the server which files to load. This is done with a simple PHP script, there really isn't anything difficult to it.

The rules are simple: 

- You provide a preload script and link to it in your php.ini file using `opcache.preload`
- Every PHP file you want to be preloaded should be passed to `opcache_compile_file()` or be required once, from within the preload script

Say you want to preload a framework, Laravel for example. Your script will have to loop over all PHP files in the `vendor/laravel` directory, and include them one by one.

Here's how you'd link to this script in php.ini:

```ini
<hljs prop>opcache.preload</hljs>=/path/to/project/preload.php
```

And here's a dummy implementation:

```php
$files = /* An array of files you want to preload */;

foreach ($files as $file) {
    <hljs prop>opcache_compile_file</hljs>($file);
}
```

### Warning: Can't preload unlinked class

Hang on though, there's a caveat! In order for files to be preloaded, their dependencies — interfaces, traits and parent classes — must also be preloaded.

If there are any problems with the class dependencies, you'll be notified of it on server start up:

```txt
Can't preload unlinked class 
<hljs type>Illuminate\Database\Query\JoinClause</hljs>: 
Unknown parent 
<hljs type>Illuminate\Database\Query\Builder</hljs>
```

See, `opcache_compile_file()` will parse a file, but not execute it. This means that if a class has dependencies that aren't preloaded, itself can also not be preloaded.

This isn't a fatal problem, your server will work just fine; but you won't have all the preloaded files you actually wanted.

Luckily, there's a way to ensure linked files are loaded as well: instead of using `opcache_compile_file` you can use `require_once`, and let the registered autoloader (probably composer's) take care of the rest.

```php
$files = /* All files in eg. vendor/laravel */;

foreach ($files as $file) {
    require_once($file);
}
```

There are some caveats still. If you're trying to preload Laravel for example, there are some classes within the framework that have dependencies on other classes that don't exist yet. For example, the filesystem cache class `\Illuminate\Filesystem\Cache` has a dependency on `\League\Flysystem\Cached\Storage\AbstractCache`, which might not be installed in your project if you're never using filesystem caches. 

You might run into "class not found" errors trying to preload everything. Luckily, in a default Laravel installation, there's only a handful of these classes, which can easily be ignored.
For convenience, I wrote a little [preloader class](*https://github.com/brendt/laravel-preload/blob/master/preload.php) to make ignoring files more easy, here's what it looks like:

```php
class Preloader
{
    private <hljs type>array</hljs> $ignores = [];

    private static <hljs type>int</hljs> $count = 0;

    private <hljs type>array</hljs> $paths;

    private <hljs type>array</hljs> $fileMap;

    public function __construct(<hljs type>string</hljs> ...$paths)
    {
        $this->paths = $paths;

        // We'll use composer's classmap
        // to easily find which classes to autoload,
        // based on their filename
        $classMap = require __DIR__ . '/vendor/composer/autoload_classmap.php';

        $this->fileMap = <hljs prop>array_flip</hljs>($classMap);
    }
    
    public function paths(<hljs type>string</hljs> ...$paths): Preloader
    {
        $this->paths = <hljs prop>array_merge</hljs>(
            $this->paths,
            $paths
        );

        return $this;
    }

    public function ignore(<hljs type>string</hljs> ...$names): Preloader
    {
        $this->ignores = <hljs prop>array_merge</hljs>(
            $this->ignores,
            $names
        );

        return $this;
    }

    public function load(): void
    {
        // We'll loop over all registered paths
        // and load them one by one
        foreach ($this->paths as $path) {
            $this-><hljs prop>loadPath</hljs>(<hljs prop>rtrim</hljs>($path, '/'));
        }

        $count = self::$count;

        echo "[Preloader] Preloaded {$count} classes" . PHP_EOL;
    }

    private function loadPath(<hljs type>string</hljs> $path): void
    {
        // If the current path is a directory,
        // we'll load all files in it 
        if (<hljs prop>is_dir</hljs>($path)) {
            $this-><hljs prop>loadDir</hljs>($path);

            return;
        }

        // Otherwise we'll just load this one file
        $this-><hljs prop>loadFile</hljs>($path);
    }

    private function loadDir(<hljs type>string</hljs> $path): void
    {
        $handle = <hljs prop>opendir</hljs>($path);

        // We'll loop over all files and directories
        // in the current path,
        // and load them one by one
        while ($file = <hljs prop>readdir</hljs>($handle)) {
            if (<hljs prop>in_array</hljs>($file, ['.', '..'])) {
                continue;
            }

            $this-><hljs prop>loadPath</hljs>("{$path}/{$file}");
        }

        <hljs prop>closedir</hljs>($handle);
    }

    private function loadFile(<hljs type>string</hljs> $path): void
    {
        // We resolve the classname from composer's autoload mapping
        $class = $this->fileMap[$path] ?? null;

        // And use it to make sure the class shouldn't be ignored
        if ($this-><hljs prop>shouldIgnore</hljs>($class)) {
            return;
        }

        // Finally we require the path,
        // causing all its dependencies to be loaded as well
        require_once($path);

        self::$count++;

        echo "[Preloader] Preloaded `{$class}`" . PHP_EOL;
    }

    private function shouldIgnore(?<hljs type>string</hljs> $name): bool
    {
        if ($name === null) {
            return true;
        }

        foreach ($this->ignores as $ignore) {
            if (<hljs prop>strpos</hljs>($name, $ignore) === 0) {
                return true;
            }
        }

        return false;
    }
}
```

By adding this class in the same preload script, we're now able to load the whole Laravel framework like so:

```php
// …

(new <hljs type>Preloader</hljs>())
    -><hljs prop>paths</hljs>(__DIR__ . '/vendor/laravel')
    -><hljs prop>ignore</hljs>(
        <hljs type>\Illuminate\Filesystem\Cache</hljs>::class,
        <hljs type>\Illuminate\Log\LogManager</hljs>::class,
        <hljs type>\Illuminate\Http\Testing\File</hljs>::class,
        <hljs type>\Illuminate\Http\UploadedFile</hljs>::class,
        <hljs type>\Illuminate\Support\Carbon</hljs>::class,
    )
    -><hljs prop>load</hljs>();
```

## Does it work?

That's of course the most important question: were all files correctly loaded? You can simply test it by restarting the server, and dump the output of `opcache_get_status()` in a PHP script. You'll see it has a key called `preload_statistics`, which will list all preloaded functions, classes and scripts; as well as the memory consumed by the preloaded files.

## Composer support

One promising feature is probably an automated preloading solution based on composer, which is used by most modern day PHP projects already.
People are working to add a preload configuration option in `composer.json`, which in turn will generate the preload file for you! At the moment, this feature is still a work in progress, but you can follow it [here](*https://github.com/composer/composer/issues/7777). 

**Update 2019-11-29**: composer support has stopped, as can be read by [Jordi's](*https://github.com/composer/composer/issues/7777#issuecomment-559725760) answer.

## Server requirements

There's two more important things to mention about the devops side when using preloading.

You already know that you need to specify an entry in php.ini in order for preloading to work. This means that if you're using shared hosting, you won't be able to freely configure PHP however you want. 
In practice, you'll need a dedicated (virtual) server to be able to optimise the preloaded files for a single project. So keep that in mind.

Also remember you'll need to restart the server (`php-fpm` is sufficient if you're using it) every time you want to reload the in-memory files. This might seem obvious for most, but still worth the mention.

## Performance

Now to the most important question: does preloading actually improve performance?

The answer is yes, of course: Ben Morel shared some benchmarks, which can be found in the same [composer issue](*https://github.com/composer/composer/issues/7777#issuecomment-440268416) linked to earlier.
I also did my own benchmarks within a real-life Laravel project. You can read about them [here](/blog/php-preload-benchmarks).

Interestingly enough, you could decide to only preload "hot classes" — classes that are used often in your codebase. Ben's benchmarks shows that only loading around 100 hot classes, actually yields better performance gains than preloading everything. It's a difference of a 13% and 17% performance increase.

Which classes should be preloaded relies of course on your specific project. It would be wise to simply preload as much as possible at the start. If you really need the few percentage increases, you would have to monitor your code while running. 

All of this can of course also be automated, and will probably be done in the future.

For now, most important to remember is that composer will add support, so that you don't have to make preload files yourself, and that this feature is very easy to setup on your server, given that you've got full control over it.

{{ cta:mail }}

Will you be using preloading once PHP 7.4 arrives? Any remarks or thoughts after reading this post? Let me know via [Twitter](*https://twitter.com/brendt_gd) or [e-mail](mailto:brendt@stitcher.io).


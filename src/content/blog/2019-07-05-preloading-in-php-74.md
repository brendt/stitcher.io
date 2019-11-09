((PHP 7.4)) adds preloading support, a feature that could improve the performance of your code significantly. 

This is preloading in a nutshell:

- In order to preload files, you need to write a custom ((PHP)) script
- This script is executed once on server startup
- All preloaded files are available in memory for all requests
- Changes made to the source file won't have any effect, until the server is restarted

Let's look at it in depth.

## Opcache, but more

While preloading is built on top of opcache, it's not exactly the same.
Opcache will take your ((PHP)) source files, compile it to "opcodes", and store those compiled files on disk.

You can think of "opcodes" as a low-level representation of your code, that can be easily interpreted at runtime.
So opcache skips the translation step between your source files and what the ((PHP)) interpreter actually needs at runtime. A huge win!

But, there's more to be gained. Opcached files don't know about other files. If you've got a class `A` extending from class `B`, you'd still need to link them together at runtime. Furthermore, opcache performs checks to see whether the source files were modified, and will invalidate its caches based on that.

So this is where preloading comes into play: it will not only compile source files to opcodes, but also link related classes, traits and interfaces together. It will then keep this "compiled" blob of runnable code — that is: code usable by the ((PHP)) interpreter — in memory.

When a request arrives at the server, it can now use parts of the codebase that were already loaded in memory, without any overhead.

So, what "parts of the codebase" are we talking about?

## Preloading in practice

For preloading to work, you, the developer, have to tell the server which files to load. This is done with a simple ((PHP)) script, so there's nothing to be afraid of.

The rules are simple: 

- You provide a preload script and link to it in your php.ini file using `opcache.preload`
- Every ((PHP)) file you want to be preloaded should be passed to `opcache_compile_file()`, from within the preload script

Say you want to preload a framework, Laravel for example. Your script will have to loop over all ((PHP)) files in the `vendor/laravel` directory, and include them one by one.

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

Note that instead of using `opcache_compile_file`, you can also `include` the file. There seems to be [a bug](*https://bugs.php.net/bug.php?id=78240) though, because as of writing this doesn't seem to work.

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

This is why you should take care of which files to preload, to make sure all dependencies are resolved.
Doing this manually might seem like a chore, so naturally people are already working on automated solutions.

## Composer support

The most promising automated solution is coming from composer, which is used by most modern day ((PHP)) projects already.

People are working to add a preload configuration option in `composer.json`, which in turn will generate the preload file for you! Just like preloading itself, this feature is still a work in progress, but can follow it [here](*https://github.com/composer/composer/issues/7777). 

Luckily, you won't need to manually configure preload files if you don't want to, composer will be able to do it for you.

## Server requirements

There's two more important things to mention, about the devops side when using preloading.

You already know that you need to specify an entry in php.ini in order for preloading to work. This means that if you're using shared hosting, you won't be able to freely configure ((PHP)) however you want. 

In practice, you'll need a dedicated (virtual) server to be able to optimise the preloaded files for a single project. So keep that in mind.

Also remember you'll need to restart the server (`php-fpm` is sufficient if you're using it) every time you want to reload the in-memory files. This might seem obvious for most, but still worth the mention.

## Performance

Now to the most important question: does preloading actually improve performance?

The answer is yes, of course: Ben Morel shared some benchmarks, which can be found in the same [composer issue](*https://github.com/composer/composer/issues/7777#issuecomment-440268416) linked to earlier.

Interestingly enough, you could decide to only preload "hot classes": classes that are used often in your codebase. Ben's benchmarks shows that only loading around 100 hot classes, actually yields better performance gains than preloading all. It's a difference of a 13% and 17% performance increase.

What classes should be preloaded relies of course on your specific project. It would be wise to simply preload as much as possible at the start. If you really need the few percentage increases, you would have to monitor your code while running. 

All of this can of course also be automated, and will probably be done in the future.

For now, most important to remember is that composer will add support, so that you don't have to make preload files yourself, and that this feature is very easy to setup on your server, given that you've got full control over it.

---

Will you be using preloading once ((PHP 7.4)) arrives? Any remarks or thoughts after reading this post? Let me know via [Twitter](*https://twitter.com/brendt_gd) or [e-mail](mailto:brendt@stitcher.io).

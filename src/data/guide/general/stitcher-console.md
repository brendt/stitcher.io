The Stitcher console is a PHP application which can be run from the commandline. The console adds some useful commands 
 to help you create and manage your website.
 
The console can be run like this.

```sh
./stitcher

# Or with a specific PHP binary
php stitcher
```

### Generate

You'll probably use this generate command the most. This command will take all your source files, and stitches them together.

```sh
./stitcher site:generate [<route>]
```

### Cleanup

This command will clean all cache and generated file.

```sh
./stitcher site:clean [--force]
```

### Routing

The router commands can be useful when debugging.

```sh
./stitcher router:list [<filter>]
./stitcher router:dispatch <url>
```

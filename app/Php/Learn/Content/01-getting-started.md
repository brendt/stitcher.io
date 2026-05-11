# Getting Started

One of PHP's advantages is that you don't need a complex setup to run it. You only have to download the interpreter, and you're good to go. Installing PHP is done with one command, in the [deploy](/php/learn/deploy) chapter, we'll discuss more complex setups for running PHP on production-ready environments.

{{ x-php-install }}

## Running your first PHP program

PHP is an interpreted language similar to JavaScript, which means it needs no explicit compilation step. Don't be mistaken: PHP's interpreter will compile your code to _opcodes_ (an optimized version of your program, similar to bytecode in Java); but all of that behind the scenes. Writing your first PHP program is as easy as create a new file, and running it:

```php
<?php // index.php

echo 'Hello world';
```

```shell
$ php index.php
# Hello world
```
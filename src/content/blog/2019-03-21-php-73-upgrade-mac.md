## Upgrading with Homebrew

Start by making sure brew is up-to-date:

```bash
brew update
```

Next, upgrade PHP:

```bash
brew upgrade php
```

Check the current version by running `php -v`: 

```bash
php -v

# PHP 7.3.3 (cli) (built: Mar  8 2019 16:42:07) ( NTS )
# Copyright (c) 1997-2018 The PHP Group
# Zend Engine v3.3.3, Copyright (c) 1998-2018 Zend Technologies
#     with Zend OPcache v7.3.3, Copyright (c) 1999-2018, by Zend Technologies
```

Restart Nginx or Apache:

```bash
sudo nginx -s reload
```

```bash
sudo apachectl restart
```

And make sure that your local web server also uses PHP 7.3 by visiting this script:

```php
# index.php, accessible to your web server

phpinfo(); die();
```

The version should show `7.3.x`.

Note: if you're using Laravel Valet, please keep on reading, 
you need some extra steps in order for the web server to properly work. 

## `JIT compilation failed` error

You might notice this error showing up when running PHP scripts, for example: `composer global update`.

```
PHP Warning: preg_match(): JIT compilation failed
```

This is due to a [PHP 7.3 bug](*https://bugs.php.net/bug.php?id=77260), 
and can easily be solved by making a change in your PHP ini file.

If you don't know which ini file is used, you can run the following:

```bash
php --ini

# Configuration File (php.ini) Path: /usr/local/etc/php/7.3
# Loaded Configuration File:         /usr/local/etc/php/7.3/php.ini
# Scan for additional .ini files in: /usr/local/etc/php/7.3/conf.d
# Additional .ini files parsed:      /usr/local/etc/php/7.3/conf.d/ext-opcache.ini,
# /usr/local/etc/php/7.3/conf.d/php-memory-limits.ini
```

Solving the above error can be done by manually disabling the `pcre.jit` option in our ini file. 

```diff
# /usr/local/etc/php/7.3/php.ini

- ;pcre.jit=1
+ pcre.jit=0
```

## Extensions

You may have heard of Homebrew dropping support for PHP extensions, 
this should now be done with PECL. 
I personally use Imagick, Redis and Xdebug. 

They can be installed like so:

```bash
pecl install imagick
pecl install redis
pecl install xdebug
``` 

You can run `pecl list` to see which extensions are installed:

```bash
pecl list

# Installed packages, channel pecl.php.net:
# =========================================
# Package Version State
# imagick 3.4.3   stable
# redis   4.3.0   stable
# xdebug  2.7.0   stable
```

You can search for other extensions using `pecl search`:

```bash
pecl search pdf

# Retrieving data...0%
# ..
# Matched packages, channel pecl.php.net:
# =======================================
# Package Stable/(Latest) Local
# pdflib  4.1.2 (stable)        Creating PDF on the fly with the PDFlib library
```

Make sure to restart your web server after installing new packages:

```bash
sudo nginx -s reload
```

```bash
sudo apachectl restart
```

## Valet

If you're using Laravel Valet, you should do the following steps to upgrade it:

```bash
composer global update
```

Note that if you're upgrading Valet from 2.0 to 2.1, your Valet config folder will automatically be moved from
`~/.valet` to `~/.config/valet`. 

Now run `valet install`

```bash
valet install
```

If you have any paths pointing to this folder, you check them.
I, for example, have a custom Nginx config file for one of my local sites.
This config file contained absolute paths to the Valet socket.
These had to be manually changed.

If you're running into problems with Nginx, you can check out the errors in the logs:

```txt
cat /usr/local/var/log/nginx/error.log
```

If any changes were made to your Valet config, you should restart it:

```bash
valet restart
```

## Last step

Finally you should test and upgrade your projects for [PHP 7.3 compatibility](*/blog/new-in-php-73). 

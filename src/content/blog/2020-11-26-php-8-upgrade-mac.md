Note: PHP 8 hasn't been released yet, so it'll be a few more hours before you can use Homebrew to update to it.

{{ ad:carbon }}

## Upgrading with Homebrew

Start by making sure brew is up-to-date:

```bash
brew update
```

Next, upgrade PHP. You can either use the built-in php recipe, use tap `shivammathur/homebrew-php`. I'd recommend the second approach, since it allows you to easily install several PHP versions and switch between them.

### Normal upgrade

```bash
brew upgrade php
```

### Upgrade with `shivammathur/homebrew-php`

```bash
brew tap shivammathur/php
brew install shivammathur/php/php@8.0
```

To switch between versions, use the following command:

```bash
brew link --overwrite --force php@8.0
```

You can read more in the [repository](*https://github.com/shivammathur/homebrew-php).

### Next steps

Check the current version by running `php -v`: 

```bash
php -v
```

Restart Nginx or Apache:

```bash
sudo nginx -s reload
```

```bash
sudo apachectl restart
```

And make sure that your local web server also uses PHP 8 by visiting this script:

```php
# index.php, accessible to your web server

<hljs prop>phpinfo</hljs>();
```

<em class="small center">The version should show `8.0.x`.</em>

Note: if you're using Laravel Valet, please keep on reading, 
you need some extra steps in order for the web server to properly work. 

{{ cta:flp }}

## Valet

If you're using Laravel Valet, you should do the following steps to upgrade it:

```bash
composer global update
```

Now run `valet install`:

```bash
valet install
```

## Extensions

PHP extensions are installed using pecl. I personally use Imagick, Redis and Xdebug. They can be installed like so:

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
# imagick 3.4.4   stable
# redis   5.1.1   stable
# xdebug  2.8.0   stable
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

If you're using Laravel Valet, you should restart it as well.

```bash
valet restart
```

Make sure all extensions are correctly installed and loaded by checking both your PHP webserver and CLI installs:

```bash
php -i | grep redis
```

```php
<hljs prop>var_dump</hljs>(<hljs prop>extension_loaded</hljs>('redis'));
```

If extensions aren't properly loaded, there are two easy fixes.

First, make sure the extensions are added in the correct ini file. You can run `php --ini` to know which file is loaded:

```
Configuration File (php.ini) Path: <hljs blue>/usr/local/etc/php/7.4</hljs>
Loaded Configuration File:         /usr/local/etc/php/7.4/php.ini
Scan for additional .ini files in: /usr/local/etc/php/7.4/conf.d
Additional .ini files parsed:      /usr/local/etc/php/7.4/conf.d/ext-opcache.ini,
/usr/local/etc/php/7.4/conf.d/php-memory-limits.ini
```

Now check the ini file:

```ini
extension="redis.so"
extension="imagick.so"
zend_extension="xdebug.so"
```

Note that if you're testing installed extensions via the CLI, you don't need to restart nginx, apache or Valet when making changes to ini settings.

The second thing you can do, if you're updating from an older PHP version which also used pecl to install extension; is to reinstall every extension individually.

```bash
pecl uninstall imagick
pecl install imagick
```

## Last step

Finally you should test and upgrade your projects for [PHP 8 compatibility](/blog/new-in-php-8). 

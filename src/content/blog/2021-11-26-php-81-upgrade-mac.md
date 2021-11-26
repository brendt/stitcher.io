<div class="footnote author">
At the moment, the brew formula still needs updating. You won't be able to properly install PHP 8.1 using brew before <a href="https://github.com/Homebrew/homebrew-core/pull/89973">this PR</a> is merged.
</div>

{{ cta:dynamic }}

## Upgrading with Homebrew

Start by making sure brew is up-to-date:

```bash
brew update
```

Next, upgrade PHP. You can either use the built-in php recipe, but I recommend to use the `shivammathur/homebrew-php` tap.

### Normal upgrade

```bash
brew upgrade php
```

### Upgrade with `shivammathur/homebrew-php`

```bash
brew tap shivammathur/php
brew install shivammathur/php/php@8.1
```

To switch between versions, use the following command:

```bash
brew link --overwrite --force php@8.1
```

You can read more in the [repository](*https://github.com/shivammathur/homebrew-php).

### Next steps

Check the current version by running `php -v`: 

```bash
php -v
```

Restart Nginx or Apache, if you're using Laravel Valet you can skip to the next section; you need some extra steps in order for the web server to properly work.

```bash
sudo nginx -s reload
```

```bash
sudo apachectl restart
```

And make sure that your local web server also uses PHP 8.1 by visiting this script:

```php
# index.php, accessible to your web server

<hljs prop>phpinfo</hljs>();
```

<em class="small center">The version should show `8.1.x`.</em>

{{ cta:mail }}

## Valet

If you're using Laravel Valet, you should do the following steps to upgrade it:

```bash
composer global update
```

You can use `valet use` to switch between PHP versions:

```bash
valet use php@8.1
valet use php@8.0
```

## Extensions

PHP extensions are installed using pecl. I personally use Redis and Xdebug. They can be installed like so:

```bash
pecl install redis
pecl install xdebug
```

You can run `pecl list` to see which extensions are installed:

```bash
pecl list

# Installed packages, channel pecl.php.net:
# =========================================
# Package Version State
# redis   5.3.4   stable
# xdebug  3.1.1   stable
```

You can search for other extensions using `pecl search`:

```bash
pecl search pdf

# Retrieving data...0%
# ..
# Matched packages, channel pecl.php.net:
# =======================================
# Package Stable/(Latest) Local
# pdflib  4.1.4 (stable)        Creating PDF on the fly with the PDFlib library
```

Make sure to restart your web server after installing new packages:

```bash
sudo nginx -s reload
```

```bash
sudo apachectl restart
```

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

```txt
Configuration File (php.ini) Path: <hljs blue>/opt/homebrew/etc/php/8.1</hljs>
Loaded Configuration File:         /opt/homebrew/etc/php/8.1/php.ini
Scan for additional .ini files in: /opt/homebrew/etc/php/8.1/conf.d
Additional .ini files parsed:      /opt/homebrew/etc/php/8.1/conf.d/error_log.ini,
/opt/homebrew/etc/php/8.1/conf.d/ext-opcache.ini,
/opt/homebrew/etc/php/8.1/conf.d/php-memory-limits.ini
```

Now check the ini file:

```ini
extension="redis.so"
zend_extension="xdebug.so"
```

Note that if you're testing installed extensions via the CLI, you don't need to restart nginx, apache or Valet when making changes to ini settings.

The second thing you can do, if you're updating from an older PHP version which also used pecl to install extension; is to reinstall every extension individually.

```bash
pecl uninstall redis
pecl install redis
```

## Last step

Finally you should test and upgrade your projects for [PHP 8 compatibility](/blog/new-in-php-81). 

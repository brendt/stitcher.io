{{ ad:carbon }}

## Upgrading with Homebrew

<div class="author">
Right now, Homebrew hasn't released its PHP 7.4 version yet, but it'll be here <a href="https://github.com/Homebrew/homebrew-core/pull/47289" target="_blank" rel="noopener noreferrer">soon</a>. 
</div>

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
```

Restart Nginx or Apache:

```bash
sudo nginx -s reload
```

```bash
sudo apachectl restart
```

And make sure that your local web server also uses PHP 7.4 by visiting this script:

```php
# index.php, accessible to your web server

phpinfo(); die();
```

The version should show `7.4.x`.

Note: if you're using Laravel Valet, please keep on reading, 
you need some extra steps in order for the web server to properly work. 

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

Homebrew doesn't support the installation of PHP extensions anymore, you should use pecl instead.
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

## Last step

Finally you should test and upgrade your projects for [PHP 7.4 compatibility](*/blog/new-in-php-74). 

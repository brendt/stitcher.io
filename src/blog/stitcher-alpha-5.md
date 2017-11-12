This is the last alpha version of Stitcher. The next release will be beta for the first time, and only bugfixes and improvements will be added from now on. Alpha 5 adds the last important pieces for Stitcher to be feature-complete before a stable 1.0 release. The most important things to note are the plugin support, improved command feedbacked and the internal use of the service container.

You can read about the upcoming plugin support in [this blogpost](/blog/simplest-plugin-support). Furthermore, I'm already working on the first plugin to support a REST api. Next step is a web interface to manage your content. For developers, Stitcher 1.0 will of course be completely useable without any plugins.

Its important to note that this update has **a breaking change** which existing Stitcher projects should take into account.

### Installation

```php
composer require pageon/stitcher-core 1.0.0-alpha5
```

### Update - breaking changes

A last big refactor has been done to support more extensions in the future. This means both the `Console` and the `DevController`
 now live in a different namespace. You'll need an updated version of `stitcher` and `index.php`. This can be done with the 
 following commands.

```
rm ./stitcher
rm ./dev/index.php
cp vendor/pageon/stitcher-core/install/stitcher ./stitcher
cp vendor/pageon/stitcher-core/install/dev/index.php ./dev/index.php

# Remove the cache dir, this might be another directory depending on your configuration.
rm -r .cache/
```

### Changelog

- Add plugin support!
- Add PHP 7.0 support.
- Add Command tests for Router commands and Generate command.
- Improved meta support.
- Improved generate command feedback.
- Refactor the use of the dependency container, enabling future extensions. (See breaking changes).
- Use stable version of `pageon/html-meta`.
- Fix folder parser bug with nested folders.
- Fix with Sass compiler import paths. The Sass compiler can now also look directly in `src/css`. This is useful when doing includes and IDE auto-completion.
- Fix global meta tags not being loaded.
- Fix for meta tags on detail pages not correctly set. 

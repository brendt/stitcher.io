*Stitcher's plugin support is available as of [stitcher 1.0.0-alpha5](/blog/stitcher-alpha-5).*

In this post, you'll read about Stitcher's plugin system. It might get a bit technical, but is definitely worth the read.

Stitcher plugins are built on top of two powerful components which already exist in many modern projects.

- Composer's auto loading
- Symfony's service container

Using these two components, a plugin is no more than a composer package, telling Stitcher it should add its own classes and parameters to the existing services. It's a wonderfully simple concept, and it works like a charm. Like almost everything in Stitcher: the simpler, the better. Let's take a look at an example.

### MyPlugin

This is what a plugin's folder structure could look like.

```
MyPlugin/
	├── src/
	│   ├── My/
	│   │    ├── MyPlugin.php
	│   │    └── Service.php
	├── config.yml
	├── services.yml
	├── composer.json
	└── README.md
```

The only requirement for a package to be "a plugin" is a class implementing the `Brendt\Stitcher\Plugin\Plugin` interface. In this example, that would be `My\MyPlugin`. When this class can be autoloaded with composer, your plugin is ready!

### Plugin interface

The `Plugin` interface requires you to only implement three methods. These methods tell Stitcher where the `services.yml` and `config.yml` files are located and how to intialise the plugin. Any other binding with Stitcher is done via the service container.

```php
namespace My;

use Brendt\Stitcher\Plugin\Plugin;

class MyPlugin implements Plugin
{
    public function init() {
        return;
    }

    public function getConfigPath() {
        return __DIR__ . '/plugin.config.yml';
    }

    public function getServicesPath() {
        return __DIR__ . '/plugin.services.yml';
    }
}
```

### `init` method

The `init` method is called after all plugin config is loaded. This method can be used as a hook to add plugin configuration to existing services. An example would be adding a command to the console application.

```php
/**
 * @return void
 */
public function init() {
    /** @var Console $console */
    $console = App::get('app.console');

    $console->add(App::get('my.plugin.command.my.cmd'));
}
```

### plugin.config.yml

The name doesn't matter as long as its a yaml file. This file works exactly the same as other config files: key-value pairs can be added and will be available as parameters in the service container. Keys can be nested, but will be flattened when loaded. One thing to note is that plugins cannot override existing parameters.

Your plugin parameters can of course be overridden from within a Stitcher project.

```yaml
# ./vendor/MyPlugin/plugin.services.yml

my.plugin:
    parameter: test
```

### plugin.services.yml

Again, the name doesn't matter, but the root element must be named `services` as per Symfony's requirements. You could also add `parameters` here.

```yaml
# ./vendor/MyPlugin/plugin.services.yml

services:
    my.plugin.my.service:
        class: My\Service
        arguments: ['%my.plugin.parameter%', '%directories.src%', '@stitcher']
```

As you can see, Stitcher services and parameters are available, as well as your own.

### Loading a plugin

Finally, a plugin must be loaded into your project for it to be active. The `plugins` parameter in your project's config file is used for doing that.

```yaml
# ./config.yml

plugins:
    - My\MyPlugin
```

That's it!

## Future possibilities

This plugin system is so simple, yet it opens the possibility to add all kinds of functionality to a Stitcher project. It's an important step towards some of my own ideas; custom themes and other applications (API and CMS); and we'll discover more of its true strength in the future.

The most important thing for me is its simplicity. When looking at plugin systems in other applications, you'll often find complex setups like a virtual directory structure, a custom plugin loader, dirty file naming conventions, own package managers, etc. I wanted to use existing and proven technologies to build on top on, and keep the system as clean as possible. I believe this approach is a step towards the right direction.

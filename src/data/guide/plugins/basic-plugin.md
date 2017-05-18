You'll need two things for a Stitcher plugin to work:

- A class implementing the `Brendt\Stitcher\Plugin\Plugin` interface.
- An autoloader (most likely composer's) that can find that class. 
  
Plugins are loaded via the `config.yml` file.

```yaml
plugins:
    - Brendt\StitcherPlugin\MyPlugin
    # - ... 
```

As long as the plugin class can be autoloaded, it will work. That means a plugin can be a separate module loaded via 
 composer, or just live inside your project.
 
### The plugin class

Like said before, your own plugin class must implement the `Plugin` interface. By doing so, you must implement two static 
 methods. These methods tell Stitcher where your plugin's services and config files are located. If you're not using any
 of those two, the method may return null.
 
```php
use Brendt\Stitcher\Plugin\Plugin;

class MyPlugin implements Plugin
{
    public static function getConfigPath() {
        return __DIR__ . '/plugin.config.yml';
    }

    public static function getServicesPath() {
        return __DIR__ . '/plugin.services.yml';
    }
}
```

### Working with dependencies

Stitcher will add your plugin class to the service container with the `autowire` option set to true. That means, you're 
 able to add dynamic dependencies in your constructor.
  
```php
use Brendt\Stitcher\Application\Console;
use Brendt\StitcherPlugin\Command\MyCommand;

class MyPlugin implements Plugin
{
    public function __construct(Console $console, MyCommand $command) {
        $console->add($command);
    }
}
```

You could also use `App` to manually get and set dependencies, but this is considered an anti-pattern in PHP.


```php
use Brendt/Stitcher/App;

public function __construct() {
    $console = App::get('application.console');
    $command = App::get('my.command');
    
    $console->add($command);
}
```

If you have experience with the dependency injection pattern, you know that the possibilities are almost limitless now.
 Stitcher's core classes are written to be easily extensible. Combined with the dependency injector, you could build 
 almost anything you want.

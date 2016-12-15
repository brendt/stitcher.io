### Installation

**Note**: Stitcher is currently in alpha. This means things can break with updates!

Stitcher is installed via Composer. It requires a PHP version of at least 5.6, but 7.0 or higher is advided.

```sh
composer require brendt/stitcher:1.0.0-alpha
php vendor/brendt/stitcher/install/stitcher site:install
```

Running the `site:install` command will generate the needed directory structure to create a Stitcher project. 
 It also copies the Stitcher commandline tool to the root of your project. From now on, you can use the following command 
 to use the Stitcher console.
 
```sh
./stitcher
```

The Stitcher console offers you a set of tools to help create your website.

### Configuration

Stitcher uses YAML for configuration files. Some configuration entries are mandatory, some are optional. 
The main configuration file should be called `config.yml` and saved in the root directory of your project. A second config file
 for the developer controller is called `config.dev.yml` by default, and saved in the `dev/` directory. More about that one later.
 
 Note that running the `site:install` command already sets up the correct config files for you.

### Development setup

The developer controller can be used to generate a single URL on-the-fly. Thus enabling a developer to make changes to 
data entries, configs, templates, css, etc.; and see these changes in real-time, without the need of manually generating the website again.

**Note:** This approach takes a bit more rendering time, so web pages will be slower.

To set up the developer controller, a separate virtual host should be created, pointing at the `dev/` directory.

```xml
<VirtualHost *:80>
    DocumentRoot "path_to_project"
    ServerName dev.stitcher.local
    
    <Directory "path_to_project">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Creating a website

While the developer controller will generate pages on the fly; a production website made with Stitcher is of course 
completely generated as HTML before the website is visited.

```sh
./stitcher site:generate
```

This command will generate your website in the `directories.src` directory. It will "stitch" all templates, data, images, 
assets, etc. together into one whole result.


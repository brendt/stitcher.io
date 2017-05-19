**Note:** if you're new to Stitcher and you want to get to know Stitcher's way of development first, feel free to 
 [skip the boring part](/guide/site-configuration) and jump right into tinkering with Stitcher. When you're done hacking
 things together, you can read up on general concepts like deployment and configuration.

### Installation

Stitcher is installed via Composer. It requires PHP 7.0 or higher.

```sh
composer create-project pageon/stitcher
```

By creating a project based on the `pageon/stitcher` repo, you'll have a setup ready to go. The directory structure will
 be explained in the next page.
 
The most important tool when using Stitcher is the console, this application can be run from the commandline in the 
 project root.
 
```sh
./stitcher

# or, with a fixed PHP binary
php stitcher
```
 
The Stitcher console offers you a set of tools to help create your website.

### Configuration

Stitcher uses YAML configuration files. Some configuration entries are mandatory, some are optional. 
 The main configuration file should be called `config.yml` and saved in the root directory of your project. A second config file
 for the developer controller is called `config.dev.yml` by default, and saved in the `dev/` directory. More about that one later.
 
Note that running the `site:install` command already sets up the correct config files for you.

You're able to import configuration files from within another one. This is useful for eg. the development configuration file.
 
```yaml
imports:
    - ../config.yml
    # - ...

directories:
    # Overridden src directory
    src: ../src
```

When importing other config files, you can still override all set config entries.

### Development setup

The developer controller can be used to generate a single URL on-the-fly. Thus enabling a developer to make changes to 
data entries, configs, templates, css, etc.; and see those changes in real-time, without the need of manually generating the website again.

**Note:** This approach takes a bit more rendering time, so web pages will be slower.

To set up the developer controller, a separate virtual host should be created, pointing at the `dev/` directory.

```xml
<VirtualHost *:80>
    DocumentRoot "path_to_project/dev"
    ServerName dev.stitcher.local
    
    <Directory "path_to_project/dev">
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

The public virtual host configuration should be something like this.

```xml
<VirtualHost *:80>
    DocumentRoot "path_to_project/public"
    ServerName stitcher.local
    
    <Directory "path_to_project/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

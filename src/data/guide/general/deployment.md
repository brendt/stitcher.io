Deploying a Stitcher application can be done in several ways. But one rule is key: the files in the `public/` directory
 *are the whole website*.
 
### Deploying the public folder

The easiest way to put your website live, is to run `./stitcher site:generate` on your local machine, and put the contents
 in the public folder on your web server. This can be done via GIT, FTP, rsync, etc. The most important thing to remember
 is that Stitcher will generate multiple image variations for responsiveness. If you're working with a lot of high quality 
 images, checking them into version control can become slow quickly.
 
### Deploying the source folder

Another approach would be to only checkin your source folder, and generate the website on your web server. This approach is
 beneficial because you can eg. ignore the development and public folders, making the deploy process faster. Usually, a 
 web server is also more optimised to generate a Stitcher website. The downside of this approach is that you'll need SSH
 access on your web server to run the `site:generate` command. You could also further automate the process, by triggering
 an automatic script via GitHub, BitBucket etc.

### Environment switching

You're free to modify the `stitcher` console file, in which you could manually load a different config file depending on 
 the environment.
 
A good example would be the `stitcher` console file used for this website. This file looks for a `.env` file specifying 
 the environment.
 
```php
#!/usr/bin/env php
<?php

require './vendor/autoload.php';

use Brendt\Stitcher\App;

$config = trim(@file_get_contents(__DIR__ . '/.env'));
App::init($config ? $config : './config/config.yml')::get('app.console')->run();
```

### CDN files

By default, Stitcher will try and parse all files it encounters. Depending on the file type, Stitcher will do different
 things with that file. However, there are many situations in which you want to expose a file as-is. Eg: an CSS library,
 a custom font, a logo, a minified JS file,... You can configure these files individually, or specify one or more folders to expose.
 
```yaml
# config.yml

caches.cdn: true
cdn:
    - css/lib/
    - js/lib/jquery.min.js
```

The following config shows the enabled CDN config (which you would almost always want on), and a folder and file to expose.
 These files are expected to be relative to the `directories.src` folder, and will be exposed in the `directories.public`
 folder.

This would be an example on how the jquery script could be loaded.

```html
<script src="/js/lib/jquery.min.js"></script>
```

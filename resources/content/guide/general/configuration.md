This is a list of all configuration values, with their defaults. Configuration keys can be nested, but will be flattened
 by Stitcher during compile time.
 
```yaml
# imports:
#    - ./my_main_config.yml

# plugins:
#    - My\Plugin\Namespace\MyPlugin

environment: production

async: false

directories:
    src: ./src

    public: ./public

    cache: ./cache

    template: ./src/template

engines:
    template: smarty

    image: gd

    optimizer: true

    minifier: true

cache:
    images: true

    cdn: true

meta:
    viewport: width=device-width, initial-scale=1

redirect.www: true

redirect.https: true

# sitemap.url: https://www.stitcher.io

# cdn:
#    - lib/js/file.js
#    - lib/css/

```

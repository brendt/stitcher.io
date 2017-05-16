This is a list of all configuration values, with their defaults. Configuration keys can be nested, but will be flattened
 by Stitcher during compile time.
 
```yaml
# See your own config.yml file for thorough documentation

imports: []
plugins: []
cdn: []
meta: []

environment: development
directories.src: ./src
directories.public: ./public
directories.cache: ./.cache
directories.htaccess: ./public/.htaccess

minify: false

engines.template: smarty
engines.image: gd
engines.optimizer: true
engines.async: true

caches.image: true
caches.cdn: true

optimizer.options: []
```

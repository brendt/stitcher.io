```
git clone 
cd stitcher-demo
composer install
cp vendor/brendt/stitcher/install/stitcher .
```

config.yml

```yaml
directories:
    # The source directory containing
    src: ./src

    # The public directory in which the site is generated
    public: ./public

    # Cache directory used by smarty
    cache: ./cache

    # Template directory
    template: ./src/template

# Configure meta tags
meta:
    viewport: width=device-width, initial-scale=1
    chartset: utf-8
    description: High performance, static websites for PHP developers
    og:description: High performance, static websites for PHP developers

# Minify CSS and JavaScript
minify: true

# Configure engines
engines:
    # Choose the template engine. Available options: smarty, twig
    template: smarty

    # Use gd as image engine. Possible configurations: `gd` or `imagick`.
    image: imagick

    # Try to use several image optimizers
    optimizer: true

caches:
    # Enable image caching while rendering
    image: true
```
Stitcher adds template functions to help build your website. These functions are always available in every template engine.
Right now, Stitcher supports two template engines: Smarty and Twig. Which template engine you want to use can be configured in `config.yml`.

```yaml
# config.yml

engines:
    template: smarty
    # template: twig
```

### Template functions

#### Meta tags

```twig
<head>
    {{ meta() }}
</head>
```

```smarty
<head>
    {meta extra=[]}
</head>
```

Render all configured meta tags. Meta tags can be added in `config.yml`; are automatically added from `meta` variables in 
 pages and entries; are parsed from `title`, `description` and `image` fields; and finally, the `extra` variable can be 
 added within the template to add even more meta.
 
```yaml
# config.yml

meta:
    viewport: width=device-width, initial-scale=1
```

#### CSS

```twig
<head>
    {{ css('main.scss', true) }}
    {{ css('main.scss', false, true) }}
    {{ css('extra.css') }}
</head>
```
```smarty
<head>
    {css src='main.scss' inline=true}
    {css src='main.scss' push=true}
    {css src='extra.css'}
</head>
```

The CSS function can take a normal CSS, Sass or SCSS file, and renders it; either inline for critical CSS, via a separate request or by enabling HTTP/2 server push.

**Note: ** when the `minify` option is set to true, files loaded with the `css` function will be minified.

```yaml
# config.yml

minify: true
```

#### JavaScript

```twig
<body>
    {{ js('main.js', true) }}
    {{ js('extraAsync.js', false, true) }}
    {{ js('extraPushed.js', false, false, true) }}
    {{ js('extra.js') }}
</body>
```
```smarty
<body>
    {js src='main.js' inline=true}
    {js src='extraAsync.js' async=true}
    {js src='extraPushed.js' push=true}
    {js src='extra.js'}
</body>
```

Load a JavaScript file, either inline, async, pushed or normal.

**Note: ** when the `minify` option is set to true, files loaded with the `js` function will be minified.

```yaml
# config.yml

minify: true
```

#### Images

```twig
<body>
    {% set image = image('img/blue.jpg') %}
    <img src="{{ image.src }}" srcset="{{ image.srcset }}">
</body>
```
```smarty
<body>
    {image src='img/blue.jpg' var='image' push=true}
    <img src="{$image.src}" srcset="{$image.srcset}">
</body>
```

Create a new image object from a path. By setting the `push` parameter, this image will be pushed when HTTP/2 is enabled on your server. Take note that pushing images might not be the best idea when working with `srcset`.

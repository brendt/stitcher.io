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

```html
<head>
    {meta}
</head>
```

Render all configured meta tags. Meta tags can be added in `config.yml`.
 
```yaml
# config.yml

meta:
    viewport: width=device-width, initial-scale=1
```

#### CSS

```html
<head>
    {css src='main.scss' inline=true}
    {css src='extra.css'}
</head>
```

The CSS function can take a normal CSS, Sass or SCSS file, and render it; either inline for critical CSS, or via a separate request.

**Note: ** when the `minify` option is set to true, files loaded with the `css` function will be minified.

```yaml
# config.yml

minify: true
```

#### JavaScript

```html
<body>
    {js src='main.js' inline=true}
    {js src='extraAsync.js' async=true}
    {js src='extra.js'}
</body>
```

Load a JavaScript file, either inline, async or normal.

**Note: ** when the `minify` option is set to true, files loaded with the `js` function will be minified.

```yaml
# config.yml

minify: true
```

#### Images

```html
<body>
    {image src='img/blue.jpg' var='image'}
    <img src="{$image.src}" srcset="{$image.srcset}" alt="">
</body>
```

Create a new image object from a path.

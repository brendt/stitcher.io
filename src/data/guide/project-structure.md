A Stitcher project consists of three base directories: `src/`, `dev/` and `public/`. The `src/` directory contains all 
the files needed to build your site. The `public/` is the public site directory, in which all pages will be rendered or 
"stitched" together. In addition to these two directories, the `dev/` directory will be useful in many cases during development. 

A basic Stitcher site might look something like this.

```
.
├── public/
│   └── .htaccess
├── dev/
│   ├── .htaccess
│   ├── config.dev.yml
│   └── index.php
├── src/
│   ├── css/
│   ├── data/
│   ├── img/
│   ├── js/
│   ├── site/
│   └── template/
├── config.yml
└── stitcher
```

### Site

The `src/site/` directory is used for yaml files describing the pages of the website. Each yaml file (default `site.yml`) 
holds be a collection of routes for pages and their configuration. A basic example would be the following.

```yaml
/:
    template: home

/guide:
    template: guide
    data:
        title: Guide
        
/guide/setting-up:
    template: guide.page
    variables:
        title: Setting up
        content: guide/setting-up.md
        nextUrl: /guide/project-structure
        nextTitle: Project structure
```

### Data

The `src/data/` directory is used to store all kinds of different data files. Data entries can be provided in many formats: 
JSON, YAML, MarkDown, image, folder,.. A data file can either contain data of a single entry, or a collection of multiple entries. 
In the second case, when using JSON or YAML files, An extra root key `entries` is required.

Examples of data usage can be found in [the next chapter](/guide/working-with-data).

### Templates

Stitcher offers support for two template engines: smarty and twig. Both have the same helper functions available.
Which engine you want to use is up to you, and configured in `config.yml`.

```yaml
engines:
    template: smarty
```

Templates are stored by default in the `src/template/` directory, but this path can be changed in `config.yml`.
 
```yaml
directories:
    template: ./src/my_templates
```

### Images, JS and CSS

Stitcher provides many helper functions to load different asset files. There's support for responsive image rendering, sass or scss compiling,
 minification, inline script loading and more. For now, the most important thing to know is that your source files should go in these folders.

A Stitcher project consists of two base directories: `src/` and `public`. The `public/` is the public site directory, 
in which all pages will be rendered or "stitched" together. The `src/` directory contains all the files needed to build your site. 
In addition to these two directories, the `dev/` directory will be useful in many cases during development. 

A basic Stitcher site might look something like this.

```
.
├── public/
│   └── .htaccess
├── src/
│   ├── css/
│   ├── data/
│   ├── img/
│   ├── js/
│   ├── site/
│   └── template/
└── stitcher
```

#### Site

The `site/` directory is used for yaml files describing the pages of the website. Each yaml file (default `site.yml`) should be 
a collection of routes for pages and their configuration. A basic example would be the following.

```yaml
/:
    template: home

/guide:
    template: guide
    data:
        title: Guide

/guide/project-structure:
    template: guide.page
    data:
        title: Project structure
        content: guide/project-structure.md
        nextUrl: /guide/setting-up
        nextTitle: Setting up
```

#### Data

The `data/` directory is used to store all kinds of different data files. Data entries can be provided in many formats: JSON, YAML, MarkDown, image, folder, ... 
A data file can either contain data of a single entry, or a collection of multiple entries. In the second case, when using JSON or YAML files, An extra root key `entries` is required.

#### Templates

Stitcher offers support for two template engines: smarty and twig. Both have the same helper functions available.
Which engine you want to use is up to you, and configured in `config.yml`.

```yaml
engines:
    template: smarty
```

#### Images, JS and CSS

Stitcher provides many helper functions to load different asset files. There's support for responsive image rendering, sass or scss compiling,
 minification, inline script loading and more. For now, the most important thing to know is that your source files should go in these folders.

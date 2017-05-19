Every page in Stitcher starts with a page configuration file. This could be one file per page, or one file containing 
 configuration for multiple pages. All these config files should reside in the `./src/site` directory. (This path can 
 differ based on the directories configuration).
 
A good default is `./src/site/site.yml`.

A page configuration file first of all takes a URL as key. The only require parameter is the `template`, in which a template
 is specified.

```yaml
/:
    template: home

/guide/{id}:
    template: guide/detail
```

Note that template extensions don't need to be added. In this case, two templates should exist: `./src/template/home.tpl`
 and `./src/guide/detail.tpl`.
 
### Other configuration

Page configuration can take a few more arguments: `variables` and `adapters`. Adapters are used to modify the page's 
 configuration, and will be discussed further down this guide. Variables can contain almost everything, a reference to a 
 data source, an image, plain text, an array of values etc. More info about data types can be found in the next chapter.
 
To give an overview of a more advanced setup, this is the `site.yml` configuration of this website.

```yaml
/:
    template: home
    variables:
        content: data/home/home.md
        blog: data/blog.yml
        news: data/blog.yml
        banner: img/home_banner.jpg
        pageCategory: home
    adapters:
        filter:
            blog:
                highlight: true
                type: blog
            news:
                highlight: true
                type: news
        order:
            blog:
                field: date
                direction: -
            news:
                field: date
                direction: -
        limit:
            blog: 2
            news: 1

/guide/{id}:
    template: guide/detail
    variables:
        page: data/guide/_guide.yml
        pages: data/guide/_guide.yml
        pageCategory: guide
    adapters:
        # A custom adapter written as a plugin for this website.
        guide:
            variable: pages
        collection:
            variable: page
            field: id

/blog:
    template: blog/overview
    variables:
        posts: data/blog.yml
        pageCategory: blog
    adapters:
        order:
            variable: posts
            field: date
            direction: -
        pagination:
            variable: posts
            entriesPerPage: 4

/blog/{id}:
    template: blog/detail
    variables:
        post: data/blog.yml
        pageCategory: blog
    adapters:
        collection:
            variable: post
            field: id
```

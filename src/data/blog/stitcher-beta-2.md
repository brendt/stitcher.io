The second beta release of Stitcher ties a lot of missing ends together, getting ready for real production sites. 

### Installation

```
composer require pageon/stitcher-core @beta
```

### Changelog

Note the a few config parameters are changed. These changes might fall under the category "breaking", 
 but were really needed in order to get a more consistent API, before a real 1.0.0 release comes along.
  
- Add Parsedown extension to support classes on `<pre>` tags in fenced code blocks.
- Disable directory listing via .htaccess.
- Add `redirect.www` and `redirect.https` options. Allowing to automatically redirect non-www to www, and http to https.
- Add `redirect` option in site config files to make a route redirect to another page.
- Use `pageon/html-meta` ^2.0 from now on. Lots of tweaks to social meta tags were added.
- Add `async` option which, when `ext-pcntl` is installed, will enable asynchronous page rendering.
- Add Parsedown extension to support `target="_blank"` links by prefixing the URL with `*`.
- Add `sitemap.xml` support. When setting the `sitemap.url` variable, a `sitemap.xml` will be generated.
- Fix bug with Collection Adapters not copying meta tags from the base page for its sub-pages.
- Add responsive images support to markdown parser.
- The following config parameters are changed (#2):
    - `caches.cdn` becomes `cache.cdn`.
    - `caches.image` becomes `cache.images`.
    - `directories.htaccess` is removed.
    - `minify` becomes `engines.minifier`
- Support multiple extensions per template engine (#7).
- Support nested conditions in the filter adapter (#1).
- Remove unused `eninges.async` option.

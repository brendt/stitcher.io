The first beta release of Stitcher has arrived. Together with a lot of bugfixes, the website has been given some more love. You'll also find a lot more documentation in the [guide](/guide/setting-up).

### Installation

The installation package, `pageon/stitcher`, now loads the beta version by default. If you're running an existing project, your should also require the beta version now: 

```
composer require brendt/stitcher 1.0.0-beta1
```

### Changelog

- Add empty array fallback in `FilterAdapter` to prevent undefined index error.
- Improved plugin initialisation support. The temporary `init` function isn't required anymore, the constructor can now be used.
- Make the adapter factory extensible.
- Improve the CollectionAdapter by adding the `browse` variable. This variable can be used to browse the detail pages. 
 It has a `next` and `prev` key which contains the next and previous entry, if there are any.
- Moved `Brendt\Stitcher\SiteParser` to `Brendt\Stitcher\Parser\Site\SiteParser` and refactored its service definition.
- Added `Brendt\Stitcher\Parser\Site\PageParser` to parse a single page, which is no longer the responsibility of `SiteParser`.
- Bugfix for general meta configuration overriding other meta values.
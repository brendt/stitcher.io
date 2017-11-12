A new alpha release has arrived for Stitcher!

This release brings a lot of optimizations and bugfixes to many parts of Stitcher. The biggest changes are found in `brendt/responsive-images`, in which a lot of bugfixes and extra options are added. Furthermore, there's one change reverted, namely the asynchronous support for image rendering. This functionality relied on several `amphp` development packages, and broke with almost every update. Async support might be readded in the future, but for now its disabled.

One of the biggest new features is the support for custom htaccess headers and with that, HTTP2 server push! This feature has been added and is tested, but not yet used in any real projects. So there's more testing to do before declaring it "stable". You can use it in almost any template function by added the `push=true` parameter.

Stitcher also uses `papgeon/html-meta` now, and will build on top of this library more and more in the future.

One final new feature is the addition of the `cdn` config parameter. This parameter takes an array of files, located in the source directory, and will copy them on-the-fly or during compile-time to the public directory. This way you can expose folders or files directly, without parsing them through Stitcher.

### Installation

The installation package, `pageon/stitcher`, still comes with `1.0.0-alpha3` by default. Feel free to manually update the composer requirement to `1.0.0-alpha4`. The default version will change as soon as HTTP/2 server push is fully tested.

Some people might need to run `composer dump-autoload -o` one more time when updating to alpha4.

### Future updates

Before this update, Stitcher was always re-tagged on the fly when new things were added. From now on, tags will only be added after a certain feature set is complete. By doing so, updating Stitcher won't break things as much as it used to do. Keep in mind Stitcher is still in alpha phase, so breaking changes will happen now and then. There's still a small feature set to be added before a first beta release will be available. Slowly but surely, we're getting there.

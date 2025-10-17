---
type: blog
title: 'Tackling responsive images - part 2'
teaserTitle: 'Responsive images (2)'
highlight: true
---

In my [previous post](/blog/tackling_responsive_images-part_1), I wrote about the idea behind integrating responsive images and Stitcher. A pretty robust library came to be. You could throw it any image, and it would generate a set of variations of that images, scaled down for multiple devices. It returned an object, which Stitcher parsed into a template variable. In templates, the following is now possible.

```html
<img src="{$image.src}" srcset="{$image.srcset}" sizes="{$image.sizes}" />
```

If you would like to read the source code instead of this post, [here you go](https://github.com/brendt/responsive-images).

Like I wrote earlier, the first version of the scaling down algorithm was based on the width of images. It worked, but it wasn't solving the actual problem: optimizing bandwidth usage. The real solution was in downscaling images based on their filesizes. The problem there: how could you know the dimensions of an image, when you know the desired filesize. This is where high school maths came into play. I was actually surprised how much fun I had figuring out this "formula". I haven't been in school for a few years, and I was rather happy I could use some basic maths skills again!

This is what I did:

```
filesize = 1.000.000
width = 1920
ratio = 9 / 16
height = ratio * width

area = width * height
 <=> area = width * width * ratio

pixelprice = filesize / area
 <=> filesize = pixelprice * area
 <=> filesize = pixelprice * (width * width * ratio)
 <=> width * width * ratio = filesize / pixelprice
 <=> width ^ 2 = (filesize / pixelprice) / ratio
 <=> width = sqrt((filesize / pixelprice) / ratio)
```

So given a constant `pixelprice`, I can calculate the required width an image needs to have a specified filesize. Here's the thing though: `pixelprice` is an approximation of what one pixel in this image costs. That's because not all pixels are worth the same amount of bytes. It heavily depends on which image codecs are used. It is however the best I could do for now, and whilst I might add some more logic in the future, I'd like to try this algorithm out for a while. 

So now the Responsive Factory scales down images by filesize instead of width. A much better metric when you're trying to reduce bandwidth usage. This is how the library is used in Stitcher:

```php
use Brendt\Image\Config\DefaultConfigurator;
use Brendt\Image\ResponsiveFactory;

$config = new DefaultConfigurator([
    'driver'      => Config::get('engines.image'),
    'publicPath'  => Config::get('directories.public'),
    'sourcePath'  => Config::get('directories.src'),
    'enableCache' => Config::get('caches.image'),
]);

$responsiveFactory = new ResponsiveFactory($config);
```

All images in Stitcher go through this factory, the factory will generate x-amount of variations of the image, and the browser decides which one it will download. Its pretty cool, and I hope it will help websites to serve more optimized images, while a developer can still focus on the most important parts of his project.

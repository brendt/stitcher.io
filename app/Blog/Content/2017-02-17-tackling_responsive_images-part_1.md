---
type: blog
title: 'Tackling responsive images - part 1'
teaserTitle: 'Responsive images (1)'
---

One of the main goals when I started with Stitcher was heavily optimized images. Looking at the [HTTP Archive](http://httparchive.org/trends.php) stats, it's clear we're doing something wrong. Luckily, the [Responsive images spec](http://responsiveimages.org/) has been made by a lot of smart people to counter the image problem. My goal was to implement this spec in Stitcher in a way that was easy enough for developers to use it to its full extent. We're not completely there yet, but we're close. In this blogpost I want to talk about the challenges I faced creating this library. And if you're more into code then into reading a blog post, [here you go](https://github.com/brendt/responsive-images).

To be clear: the goal of the responsive images spec is to reduce bandwidth used when downloading images. Images nowadays require so much bandwidth. When you think about it, it's insane to load an image which is 2000 pixels wide, when the image on screen is only 500 pixels wide. That's the issue the spec addresses, and that's the issue I wanted to solve in Stitcher.

So I want one image to go in, x-amount of the same image with varying sizes coming out, and let the browser decide which image is the best to load. How could I downscale that source image? That was the most important question I wanted answered. All other problems like accessebility in templates and how to expose the generated image files, were concerns of Stitcher itself.

My first take on downscaling images was the following:

Take the source image and a set of configuration parameters. These parameters would decide the maximum amount of image variations and the minimum width of the image. Eg. I want a maximum of ten images, with the smallest image being 300 pixels wide. Now the algorithm would loop a maximum of 10 times, always creating an image which is 10% smaller in width than the previous one.

You might already see this is not the optimal approach. After all: we're trying to reduce bandwidth used when loading images. There is no guarantee an image which is downscaled 10%, is also reduced in size. Much depends on which image codecs are used, and what's in the image itself. But by using this approach early on, I was able to implement this "image factory" with Stitcher. Next I would be working on optimizing the algorithm, but for the time being I could tackle the Stitcher integration.

## Linking with Stitcher

Letting Stitcher know about responsive images was both easy and difficult at the same time. The basic framework was already there. So I could easily create an image provider which used the responsive factory, and returned an array representation of the image. The template syntax looks like this:

```html
<img src="{$image.src}" srcset="{$image.srcset}" sizes="{$image.sizes}" />
```

Unfortunately, there is no way to automate the sizes part, unless you start crawling all CSS and basically implement a browser engine in PHP. My solution for this part is pre-defined sets of sizes. That's still a work in progress though, I'm not sure yet how to make it easy enough to use. For now, I'm just manually specifying sizes when writing template code.

But the tricky part wasn't the sizes, neither the srcset. It was handling paths and URLs. I've noticed this throughout the whole Stitcher framework: creating the right paths and URLs (correct amount of slashes, correct root directory etc.) is actually quite the pain to manage. I'm convinced by now I need some kind of helper which always renders the correct paths and URLs. It's on my todo list.

That's it for this blogpost, next up I'll be writing about optimizing the image algorithm.

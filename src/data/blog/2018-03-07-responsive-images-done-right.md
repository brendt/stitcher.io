I want to share some thoughts on responsive images.
I'll write about a certain mindset which many projects could benefit from.
Small- and mid-sized web projects, that don't need a full blown CDN setup, 
but would like to enjoy the performance gain of responsive images.

The idea behind responsive images is simple: 
try to serve an image which dimensions are as close as possible to the image dimensions on screen.
For example: if you're displaying an image on a mobile device which has a screen of 600px wide, 
there's no need for downloading that image with a width of 1000px. 

The responsive images spec handles not only media queries, but also pixel density.
The only thing the server has to do is generate multiple variations of the same image, 
each with a different size.

If you'd like to know more about how things are done behind the scenes, 
I'll share some links to interesting resources at the end of this post.

## How to render responsive images

There are different ways to render variations of the same image. 
The simplest approach could be this: 
**given an image, create 4 variations: 1980px, 1200px, 800px and 400px**.

While this approach is easy to implement, it's not the most optimal.
The goal of responsive images is to serve faster loading images, 
but still serving the best quality possible for the user's screen.
 
There are two variables in this equation: the width of the user's screen 
(and therefor the width of the image itself); but also the filesize of an image.

Say you have two images with exactly the same dimensions. 
Depending on the content in that image and the encoding used, 
their file sizes could differ a lot. 

Another approach could be to manually define the most optimal `srcset` for each image.
You know of course that this is impossible to do for most websites.
For one, a website could have a lot of images, 
but it's also difficult to know what the most optimal way of scaling down a specific image is, 
without trying out many possibilities.

Luckily, computers are very good at tedious calculations on a large scale.
This approach seems like a good idea:
**given an image, generate x-amount of variations of that image,
each variation being approximately 10% smaller in filesize**.

How does that sound? You now have a small margin of possible "overhead" 
for variable screen sizes, but at least we're sure that margin won't be more than 10%.
Depending on the size of the image, for example: a thumbnail vs. a hero image; 
we could even reduce the margin to 5% instead of 10%.

This of course will result in a different `srcset` for every image, 
but that's none of our concern, the responsive images spec can handle that for us.

This is how you would determine such variable dimensions in PHP:

```php
// $height = height of the source image
// $width = width of the source image

$dimensions = [];

$ratio = $height / $width;
$area = $width * $width * $ratio;
$pixelPrice = $fileSize / $area;
$stepModifier = $fileSize * 0.1;

while ($fileSize > 0) {
    $newWidth = floor(sqrt(($fileSize / $pixelPrice) / $ratio));

    $dimensions[] = new Dimension($newWidth, $newWidth * $ratio);

    $fileSize -= $stepModifier;
}
```

I won't go into the details of this formula in this post, 
I've [written about it](*https://www.stitcher.io/blog/tackling_repsonsive_images-part_2) before.
But I do want to make clear that this approach does not brute forces multiple variations.
It will calculate the dimensions needed for ten variations, 
each being approximately 10% smaller in filesize. 

## In practice

Let's take a look at a picture of a parrot.

This image has a fixed `srcset`:

<p>
    <img src="/img/static/parrot-fixed-800.jpg" srcset="/img/static/parrot-fixed-1920.jpg 1920w, /img/static/parrot-fixed-1200.jpg 1200w, /img/static/parrot-fixed-800.jpg 800w, /img/static/parrot-fixed-400.jpg 400w"/>
</p>

This one has a dynamic `srcset`:

![parrot](/img/blog/responsive/parrot.jpg)

Feel free to open up your inspector and play around with it in responsive mode.
Be sure to disable browser cache and compare which image is loaded on different screen sizes.

For example, on an iphone 7 screen, the fixed width image loads the 800px variant, 
while the dynamic version loads the 678px image! 

On smaller screens, a simple smartphone for example, 
there are 3 variations available for the dynamic variant, 
while all those screens get the 400px version for the other.

Could you imagine doing this be hand? 
Neither could I! Of course I implemented this rendering method into Stitcher, 
and we also implemented it at my job, Spatie; in the Laravel media library.

The usages is as simple as this.

```php
$model
   ->addMedia($yourImageFile)
   ->withResponsiveImages()
   ->toMediaCollection();
```

```html
<img src="{{ $media->getFullUrl() }}" srcset="{{ $media->getSrcset() }}" sizes="[your own logic]"/>
```

We're still working on an official version 7 release, which contains the responsive images support.
You can read more about it [here](*https://docs.spatie.be/laravel-medialibrary/v7/responsive-images/getting-started-with-responsive-images).

To finish with, here are the links which I mentioned at the start of this post. 

- Responsive images explained in depth: 
[https://ericportis.com/posts/2014/srcset-sizes/](*https://ericportis.com/posts/2014/srcset-sizes/)
- The official specification website: 
[https://responsiveimages.org/](*https://responsiveimages.org/)

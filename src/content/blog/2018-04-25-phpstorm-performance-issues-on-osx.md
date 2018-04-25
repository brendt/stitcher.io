Are you using a fancy Mac with the latest and greatest hardware, 
yet still having performance issues with PHPStorm? 
I've been struggling with this the past few months.

It turns out, the solution might be rather unexpected. 
Instead of disabling plugins, inspections and what not; 
it seems like there's an issue with font rendering in the JRE for Mac.

This means that on certain resolutions, for certain fonts and for certain kinds of antialiasing,
PHPStorm will need *a lot* of CPU power just to render fonts.
So how to fix it? There are a few options.

- Use another font. I was using Ubuntu Mono, and it turns out it requires quite a lot of CPU.
I've switched to Monaco instead.
- Disabling `Subpixel` antialiasing. Go to `Settings > Appearance & Behavior > Appearance` 
to configure antialiasing in your editor to `Greyscale` instead. 
Your fonts won't look as good, but you'll notice a huge performance improvement.
- Wait for JetBrains to find a fix. 2018.2 might fix some things, 
but the real solution will take a while. There's an active discussion on the topic [here](*https://youtrack.jetbrains.com/issue/JRE-526#u=1466510431624).

If you're looking for even more performance improvements that can be made in PHPStorm, 
take a look over [here](/blog/phpstorm-performance).  

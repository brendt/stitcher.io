PhpStorm has had performance issues on OSX for a very long time now,
sometimes to the point of being unusable.

I've written about these issues before, but it's good to keep a regularly updated list of what's going on.
So without further ado: if you're on OSX (Sierra, High Sierra or Mojave); 
if you're experiencing PhpStorm performance issues, this post might help you.

{{ ad:carbon }}

## External monitor resolution

Do you have an external monitor plugged into your MacBook? 
There's an issue in Java Swing, the UI framework that PhpStorm uses under the hood.
In short: if you're using a non-default resolution, 
Java has to do a lot of calculations to deal with half pixels and such.

In case of 4k monitors, we've seen good results with 1080p and 4k resolutions,
as they are natively supported. 
All other resolutions can cause massive performance issues.

<div class="image-noborder"></div>

![](/resources/img/blog/phpstorm-performance-october/resolution-default.png)

Default resolutions work fine.

<div class="image-noborder"></div>

![](/resources/img/blog/phpstorm-performance-october/resolution-scaled.png)

Scaled resolutions not so much…

## Font antialiasing

In your settings, under `Editor > Appearance & Bahaviour > Appearance`, 
you'll find the editor font antialiasing options.

By default, antialiasing is set to `subpixel`, to render very smooth fonts.
Again, because of Java graphical issues, there can be a big performance hit. 

![](/resources/img/blog/phpstorm-performance-october/font-settings.png)

It's better to set the antialiasing setting to `greyscale`, or disable it altogether.

Your font choice might also impact performance.
I know this might take some time to get used to, but try using another font.
I always used Ubuntu Mono, but switched to Monaco, and had noticeable improvements.

## JavaFX enabled plugins

Some plugins make use of JavaFX, that may cause rendering issues.
As an easy way to know if you're running such plugins, you can do the following.

Get the PID of the running PhpStorm process:

```bash
> top | grep phpstorm

82912  phpstorm         …
```

Next, run `jstack` with PhpStorm's process ID, and grep for "quantum":

```bash
> jstack 82912 | grep quantum

at com.sun.javafx.tk.quantum.QuantumRenderer$PipelineRunnable.run(QuantumRenderer.java:125)
```

If you see any output (as above), it means that plugins are using JavaFX.
Using these plugins will increase performance issues over time, especially if you're running PhpStorm as a maximized window.

The only way to know which plugins are using JavaFX is by disabling plugins, one by one; restarting PhpStorm and doing the above `jstack` test again.
One very popular plugin depending on JavaFX is the Markdown plugin.

## JDK versions

The last thing you can do is download a new Java JDK, another version, and use that one to run PhpStorm.

You can configure the JDK PhpStorm is using by opening the command palette and search for `Switch Boot JDK…`.

![Boot JDK](/resources/img/blog/phpstorm-performance-october/jdk.png)

It's important to note that IntelliJ products won't run on all JDKs! 
At the time of writing, Java 10 won't work yet.

If you've configured a JDK that broke PhpStorm, you can still fix it though.
There's a file in your preferences folder which contains the JDK you're using:

``` 
~/Library/Preferences/IntelliJIdea<VERSION>/idea.jdk
```

You can change the JDK path there. 
More information on switching JDKs can be found [here](*https://intellij-support.jetbrains.com/hc/en-us/articles/206544879-Selecting-the-JDK-version-the-IDE-will-run-under).

## In closing:

Software development is hard.

It's understandable why JetBrains chooses Java as a platform for their IDEs.
Unfortunately Java Swing, an older UI framework, doesn't play well with modern OSX platforms.

Whose fault is this? Should JetBrains fix it? Will they be able to?
There's no clear answer to those questions. 
There's an active issue [here](*https://youtrack.jetbrains.com/issue/JRE-526),
where you can follow the progress; 
though I doubt there will be any solutions soon.

For now, we'll have to deal with these performance issues, 
because&thinsp;—&thinsp;even though they are annoying&thinsp;—&thinsp;PhpStorm is still the best PHP IDE out there, by far. 

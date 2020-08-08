Let's just dive right in.

## Java VM options

PHPStorm is made in Java. If you ever played Minecraft, you know you could allocate extra RAM by adding 
flags to your startup command. You can also do this in PHPStorm, it's even built into the UI.

Go to `help > Edit Custom VM Options`. You can play around with the settings here. 
I for one changed the maximum amount of RAM allocated to PHPStorm, and added two graphics options 
(at the end of the file).  

```text
-Xms500m
-Xmx1500m

-Dawt.useSystemAAFontSettings=lcd
-Dawt.java2d.opengl=true

# Only for people on Mac, it makes Java use an optimised graphics engine.
-Dapple.awt.graphics.UseQuartz=true
```

{{ ad:carbon }}

## Custom properties

PHPStorm also has a file to set custom properties: `help > Edit Custom Properties`. 
Adding one option here changed the way PHPStorm renders text: it will show text immediately, 
instead of analysing it first. The downside is that you can sometimes see a flash of unstyled text.
It feels much smoother though. 

```
editor.zero.latency.typing=true
```

## Inspections and plugins

PHPStorm is a powerful IDE, with lots of functionality built in by default. While I'd highly recommend using 
these options to their full extent, there are some things that are never used. 

Disabling unused plugins can be a start, but disabling inspections has a much bigger impact. 
Take a look at the list and decide for yourself which onces you don't need: `Settings > Editor > Inspections`.

## Language injection

One plugin in particular has a big performance impact: `IntelliLang`. This plugins allows for 
languages to be recognised in different file formats. Eg. HTML autocompletion and highlighting in a PHP file.

I would not recommend completely disabling this plugin, but there might be some injections 
which you don't need in your projects: `Settings > Editor > Language Injections`.

## Project setup

Managing which files PHPStorm must index has to be done on a project level basis. 
It is worth spending 5 minutes on initial project setup, for projects you'll work hours and days on.

### Excluding directories

Go to `Settings > Directories` to mark directories as excluded. PHPStorm won't index these files.
Directories to exclude would be eg. cache, public and storage directories; 
directories which contain generated files from asset building, and last but not least: `vendor` and `node_modules`.

### The vendor problem

Excluding directories from indexing means no auto-complete from those directories. 
So excluding the vendor directory might not be the best idea.
There's a neat little trick though, which allows you to whitelist vendor directories you want to use,.

Go to `Settings > Languages & Frameworks > PHP`. In here you can set include paths. 
By manually specifying which vendor directories should be indexed, you can eliminate a lot of indexing time.
You might eg. always keep dependencies of vendors excluded, because chances are you won't be using those APIs.
If you come across a vendor you need auto-completion for, just add it to the list.

### Node modules

Node modules are "excluded" by default, but they are added as include paths nevertheless. 
Because of the size of the `node_modules` directory, it can take quite a while to index it.

JavaScript include paths are managed like PHP includes, but in `Settings > Languages & Frameworks > JavaScript > Libraries`.
I personally don't write a lot of JavaScript, so I just remove the inclusion of `node_modules` completely. 

Managing directories requires a bit of time for each project, but it's worth the performance gain in the long run.

## Font rendering on OSX

There's a confirmed issue in the JRE with certain fonts. 
While this might seem like a minor detail, certain fonts actually require a lot of processor power to render text,
slowing down PHPStorm in its whole. 

I've written a separate blog post on this issue, and how you can fix it. 
You can read it [here](/blog/phpstorm-performance-issues-on-osx).



## On a personal note

I didn't start this post by writing my own thoughts, because I figured people were looking for some quick tips to speed of their IDE.
As a PHP developer, I think that PHPStorm is such a powerful tool, which helps me to write good and maintainable code.
I don't want it to stand in my way though, so good performance is an absolute requirement.

With the things listed above, I feel that PHPStorm offers the best balance between performance and intelligence.
I've written PHP in Sublime Text for ± 5 years. I did put some time into tweaking PHPStorm to my needs,
and now I'm 100% sure I'll never go back to Sublime Text. 
My IDE is just way too smart and helpful to me. It allows me to focus on real application logic, 
instead of writing the same boilerplate code over and over again. 
I'll talk more about the benefits of an IDE over a text editor in another post. 
For now, I hope that you found these tips helpful. 

Happy coding!

---

Ready for more? I've got a new blog post full of [tips for PHPStorm users](/blog/phpstorm-tips-for-power-users)!

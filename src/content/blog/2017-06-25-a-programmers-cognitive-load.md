As a professional programmer, I'm reading and writing code on a daily basis. 
I'm working on new projects, doing code reviews, working with legacy code, reading through documentation etc. 
Based on my own experience and that of colleagues, being a programmer often involves a lot more reading than actually writing code. 

Whether it's your own code or that of others, when you open a file you have to take it all in. 
You need to wrap your head around what's going on, before you're able to write your code, do your thing.
 
Having to deal with code almost every day, it's important to find ways to make this process easy. 
To try and reduce the cognitive load it puts on your brain as much as possible. 

Making code easier to read will allow you to work faster and better, and also improve your mental state and mood.

> In cognitive psychology, cognitive load refers to the total amount of mental effort being used in the working memory - [wikipedia](*https://en.wikipedia.org/wiki/Cognitive_load)

Today I want to share some techniques that can help you reduce cognitive load while coding. 
In contrast to some recent advocates of "visual debt", I won't talk about stripping away pieces of your codebase. 
We'll look purely into the visual aspect: what makes code hard to read and reason about, and how to make it easier.

{{ ad:carbon }}

## Fonts and spacing

Fonts have an influence on our mood. 
The [people at Crew](*https://crew.co/blog/the-psychology-of-fonts/) wrote an interesting piece about how fonts make us feel. 
The font you choose has a big impact on how much load is put on your brain to process the text on your screen. 
It's not just the font by the way, but also its size, line height and letter spacing plays a role. 

Typography itself is a topic books are written about. 
I encourage you to think about your current font choice, and how it influences the way you read code.

Here's a comparison between a not-so-good and better font configuration, in my experience.

![](/resources/img/blog/cognitive-load/aestetics-1.png)

![](/resources/img/blog/cognitive-load/aestetics-2.png)

## Folding

Ever worked with a controller providing some CRUD actions? 
A class with a few methods? 
Folding your method bodies by default gives you a much clearer overview of the class when opening a file. 
It makes it easier to decide where you want to go to, instead of scrolling and searching. 
Take a look at the following example.

![](/resources/img/blog/cognitive-load/aestetics-3.png)

IDEs like IntelliJ can fold code by default: `Settings > Editor > General > Code Folding`. 
I was a bit hesitant to enable it by default, but I can assure you this is an amazing feature once you're used to it. 

It's also more convenient compared to the file structure navigator many IDEs and editors provide. 
This approach allows you to see the visual structure, color and indentation of the class. 

You'll probably want to learn the keybinds associated with folding too. On Mac with IntelliJ, these are the defaults: `⌘⇧+`, `⌘⇧-`, `⌘+` and `⌘-`. 

## Documentation

Documentation and comments are good tool to clarify what code actually does. 
Furthermore, some languages and IDEs rely on comment meta data to provide proper static analysis. 
We shouldn't overdo it though: docblocks and comments often state the obvious things, which are already known by reading the code. 

After several years of programming, I can safely say that about 80-90% of comments are redundant. 
They should be removed to clear visual overload, but you also need to provide something in their place:

- Clear naming of methods, variables, constants, etc.
- Use proper type annotations and definitions

Self documented code is better than relying on comments. 
My rule of thumb when adding a comment is asking the following question: 
"Does this comment actually add more information than already available through the code?". 
If the answer is no, the comment shouldn't be there. 

Removing these comment frees up your code, giving you visual "space to breath".

## Naming things

Another important thing to keep in mind: how to name things? 
It's better to give a variable a longer, descriptive name; rather than make them as short as possible. 

Short, cryptic names make sense at the moment of writing the code; 
but even a few days after you've written them, they already become vague and meaningless. 

Better to write a little more, than to read ten times as much to understand what you've written in the past. 

Here are a few examples from an project of mine:

- I renamed `createPage()` to `createPaginatedPage()`.
- `$process` became `$pageRenderProcess`.
- `testStitcher()` changed to multiple methods, one of which called `test_stitch_multiple_routes()`.

## Colours

This might be a sensitive topic for many people. 
I ask you to give me the benefit of the doubt though. 

We all like our dark, cool looking colour schemes, but there's a problem with them.

Research shows that the human eye is better equipped to read dark text on light backgrounds, 
than the other way around. 

Back in the 80's when personal computing was growing in popularity, 
a guy called Etienne Grandjean did an [extensive study](*https://dl.acm.org/citation.cfm?id=578434) 
on how text is best read on screens.

Now you might think that light colour schemes actually hurt your eyes, 
but this has more to do with the brightness of your screen, than the colour scheme itself.

It's true that a light colour scheme requires less brightness, if you want to avoid headaches and painful eyes.
On the other hand, a less bright screen in combination with a light colour scheme will put less load on your eyes, 
which makes reading code for long periods of time less exhaustive.

I can say what I want of course, 
but the best thing I could do is challenge you to a one-week tryout. 
I've challenged many programmers before, and most of them were actually convinced of a light theme after a few days.

It's up to you! Here's how my code looks like these days:

![](/resources/img/blog/cognitive-load/aestetics-4.png)

---

The points I listed today have almost nothing to do with how you write real code (programming logic, patterns used, etc.). 
But they have an impact on the cognitive load put on your brain day by day. 

They take away some of the pain points when writing code. 
They allow you to enjoy programming more. 

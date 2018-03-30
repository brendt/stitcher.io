As a professional programmer, I'm reading and writing code on a daily basis. I'm working on new projects, doing code reviews, working with legacy code, learning documentation etc. Based on my own experience and that of colleagues, being a programmer often involves a lot more reading than actually writing code. Whether it's your own code or that of others, when you open a file, you have to take it all in. You need to wrap your head around what's going on, before you're able to write your code. Doing this day by day, it's important to find ways to make this process easy. To try and reduce this cognitive load as much as possible. Streamlining the way you take in code, will allow you to not only work faster and better; but also improve your mental state and mood.

> In cognitive psychology, cognitive load refers to the total amount of mental effort being used in the working memory - [wikipedia](*https://en.wikipedia.org/wiki/Cognitive_load)

Today I want to share some techniques that can help you reduce this cognitive load while coding. In contrast to some recent advocates of "visual debt", I won't talk about stripping away pieces of your codebase. We'll look purely into the visual aspect: what makes code hard to read and reason about, and how to make it easier.

## Fonts and spacing

Fonts have an influence on our mood. The [people at Crew](*https://crew.co/blog/the-psychology-of-fonts/) wrote an interesting piece about how fonts make us feel. Your font choice has a big impact on how much load is put on your brain to process the text on your screen. Not only the font, but also the font size, line height and letter spacing has a role. Typography itself is a topic books are written about. I encourage you to think about your current font choice, and how it influences the way you read code.

A comparison between a not-so-good and better font configuration, in my experience.

![A not-so-good font choice](/img/blog/fonts/font-size_bad.png)

![A better font configuration](/img/blog/fonts/font-size_good.png)

## Folding

Ever worked with a controller providing some CRUD actions? A class with a few methods? Folding your method bodies by default gives you a much clearer overview of the class when opening a file. It makes it easier to decide where you want to go to, instead of scrolling and searching. Take a look at the following example.

![Folded code by default](/img/blog/fonts/folding.png)

PHPStorm can fold code by default (Settings > Editor > General > Code Folding). I was a bit hesitant to enable it by default, but I can assure you this is an amazing feature once you're used to it. It's also more convenient than the file structure navigator many IDEs and editors provide. This approach allows you to see the visual structure, color and indentation of the class. 

You'll probably want to learn the keybinds associated with folding too. On Mac with PHPStorm, these are the defaults: `⌘⇧+`, `⌘⇧-`, `⌘+` and `⌘-`. 

## DocBlocks

DocBlocks is a good tool to clarify what code actually does. Furthermore, IDEs like PHPStorm rely on certain DocBlocks. They are needed to provide correct autocomplete functionality in some cases. A frequent example is "array of objects". Yet modern PHP offers a lot of possibilities to write self-documenting code. DocBlocks often state the obvious things, which are already known by reading the code. 

Take a look again at the example above. There are no DocBlocks there. I've actually removed all redundant DocBlocks from the Stitcher core. I only kept DocBlocks which provide IDE autocomplete functionality and real documentation. I also disabled the automatic DocBlock generation in PHPStorm. 

There are two requirements for this method to work though.

- Clear naming of methods, variables, constants, etc.
- Using type hints.

It would be nice if PHP also had class variable type hints, and if we could type hint arrays. Fortunately, chances are these features will be implemented in PHP in the future.

Self documented code is better than DocBlocks. Modern PHP provides the right building blocks to write such code. My rule of thumb when adding DocBlocks is asking the following question: "Does this DocBlock actually add more information than already available through the code?". If the answer is no, the DocBlock shouldn't be there. Removing the DocBlocks frees up your code, giving you visual "space to breath".

## Naming things

The last point to keep in mind: how do you name things? It's better to give a variable a longer, descriptive name, rather than make them as short as possible. Short, cryptic names make sense at the moment of writing the code. But even a few days after you've written them, they already become vague and meaningless. Better to write a little more, than to read ten times as much to understand what you've written in the past. 

A few examples from the Stitcher core.

- `createPage()` was renamed to `createPaginatedPage()`.
- `$process` became `$pageRenderProcess`.
- `testStitcher()` changed to multiple methods, one of which called `test_stitch_multiple_routes()`.

---

The four points I listed today have almost nothing to do with how you write real code (programming logic, which patterns used, etc.). But they have an impact on the cognitive load put on your brain day by day. They take away some of the pain points when writing code. They allow you to enjoy programming more. 

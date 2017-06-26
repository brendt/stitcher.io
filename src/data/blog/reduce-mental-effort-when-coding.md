If you're a professional programmer, chances are you're reading and writing a lot code, on a daily basis. Furthermore, you're probably spending more time reading code than writing. Doing code reviews, working with legacy code, learning documentation, working on code you've written yourself, and more. Based on my own experience and that of colleagues, we're not writing that much code. We're often thinking about the codebase and how to tackle certain problems. I'll share some techniques that can be helpful when you have to take it all in and wrap your head around a big pile of code.

In contrast to some recent advocates of "visual debt", I won't talk about stripping away pieces of your codebase. We'll look purely into the visual aspect of what makes code hard to reason about, and how to make it easier.

## Fonts and spacing

Fonts have an influence on our mood. The [people at Crew](*https://crew.co/blog/the-psychology-of-fonts/) wrote an interesting piece about how fonts make us feel. When reading code, your font choice has a big impact on how much load is put on your brain to process the text on your screen. Not only the font, but also the font size, line heigt and letter spacing has a role. Typogarphy itself is a topic books are written about. I encourage you to think about your current font choice, and how it influences the way you read code.

A comparison between a not-so-good and better font configuration:

<p>
	<img src="/img/blog/fonts/font-size_bad.png" alt="A not-so-good font" class="small">
</p>

<p>
	<img src="/img/blog/fonts/font-size_good.png" alt="A better font configuration" class="small">
</p>

## Folding

Ever worked with a controller providing some CRUD actions? A class with a few methods? Folding your method bodies by default gives you a much clearer overview of the class when opening a file. It makes it much more easy to decide where you want to go to, instead of scrolling and search. Take a look at the following example.

![Folded code by default](/img/blog/fonts/folding.png)

PHPStorm can fold code by default (Settings > Editor > General > Code Folding). I was a bit hesitant to enable it by default, but I can assure you this is an amazing feature once you're used to it. It's also more convenient that the file structure navigator many IDEs and editors provide, because you're still seeing the visual structure, color and indentation of the class.

## DocBlocks

DocBlocks are a good tool to clearify what code actually does. Furthermore, IDEs like PHPStorm sometimes need DocBlocks to be able to provide correct autompletion, think "array of objects". Modern PHP however offers a lot of possibilities to write self-documenting code. More often than not, DocBlocks only state the obvious things, which are already known by reading the code. 

Take a look again at the example above. There are no DocBlocks there. I've actually removed all redundant DocBlocks from the Stitcher core. Only the DocBlocks providing IDE autompletion functionality and real documentation are kept in the codebase. I also disabled the automatic DocBlock generation in PHPStorm. 

There are two requirements for this method to work though.

- Clear naming of methods, variables, constants, etc.
- Using type hints.

It would be nice if PHP also had class variable type hints, and if we could type hint arrays. Fortunaly, there is a big chance these features will be implemented in PHP.

Self documented code is always better than DocBlocks. Modern PHP provides the right building blocks to write such code. My rule of thumb when adding DocBlocks is asking the following question: "Does this DocBlock actually add any more information than already available through the code?". If the answer is "no", the DocBlock shouldn't be there.

## Naming things

The last point to keep in mind: how do you name things? It's better to give a variable a long, descriptive name, rather than as short as possible a name. Short, cryptic names make sense at the moment of writing the code; but even a few days after you've written them, they are already vague and meaningless. Better to write a little more, than to read ten times as much to understand what you've written in the past.
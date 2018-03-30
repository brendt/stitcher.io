So many editors to choose from! Which one is the best for you? 
I can already tell you that you won’t find the answer here. But maybe I can list some pros and cons. 
We’ll be looking at Sublime Text, Github’s Atom, Adobe’s Brackets and Microsoft’s Visual Studio Code. 
All of these editors are based on the same core concepts, some of which Sublime made extremely popular. 
But there are some big and subtle differences.

## Out of the box features

All four editors are multi platform, have the command palette and fuzzy finder we’ve grown accustomed to. 
It’s important to keep in mind that Sublime and Atom are primarily focussed on packages to provide functionality, 
while Brackets and Visual Studio Code provide a more all-in-one solution from the start. 
More about packages later, here are the most important differences out of the box.

**Visual Studio Code** comes with built-in GIT support, a task runner and a linter. 
You can start to code without having to set up anything. 
It’s focussed on Node and ASP.NET development, which is reflected in the tools provided. 
But you can use it for any other language.

**Sublime Text** provides a lot of themes from the start, 
has a built in project manager and offers many customisable keybindings and commands to do text manipulation. 
There are however a lot of packages you’ll want to download immediately.

**Atom** has a package manager shipped by default. 
Atom’s file tree sidebar has some very nice features such as GIT support and file manipulation (see below). 
There’s also a live MarkDown editor which is really neat. 
But like Sublime, you’ll want to install extra packages from the start.

**Brackets** has an awesome live preview feature which just blew my mind. 
Brackets is focussed on front-end web development and provides very good tools to do so. 
It also comes with a linter, debugger, inline editor and Photoshop integration. 
There’s an extension manager available too. (That’s the Adobe version of packages, more about those later).

I felt Visual Studio Code and Brackets were really just plug-and-play from the start. 
Both Sublime and Atom require a lot of tweaking to set everything up for the best coding experience. 
This isn’t a bad thing, but in this category, Visual Studio Code and Brackets are the best.

---

<p>
    <img src="/static/resources/img/static/editors/1.png" class="editor-badge"/>
</p>

---

## Packages

Packages (or extensions, thanks Adobe), give you access to a lot of extra features.

**Brackets** has an extension manager which is rather slow and bulky and has an “Adobe feel” to it. 
You can easily install packages from a local source, URL or an online repository. 
The extension manager lacks however good package documentation.

In **Sublime**, you’ll need Package Control if you want to easily install other packages. 
There’s a very wide variety of packages available there. 
Chances are that you’ll be able to do that one thing you like with an existing package. 
Browsing packages is a bit of a pain from the command palette though. 
There are many small undocumented packages which makes it often a guess as to what a package really does. 
The online documentation isn’t user friendly either. It’s mostly a huge pile of text per package.

**Atom** shines when it comes to packages. It has a built-in package manager which works directly with GitHub. 
Not only are there a lot of packages available, there’s also a very high standard on documentation. 
You’ll be able to see screenshots, keybinding references and even animated GIFs explaining how a package works and what it does. 
All from within Atom. It’s super easy to update packages and Atom will tell you when a package is outdated or uses deprecated code. 
It shouldn’t surprise you that Atom itself is actually a collection of these same packages.

**Visual Studio Code** as of VSC V0.10.1 there’s extension support, which looks a lot like Sublime’s Package Control.
Because of the recent popularity of Visual Studio Code, there's a big plugin system rising.

Atom is a winner when it comes to packages.
The whole system is built upon the package manager, and there’s a big community behind it. 
That should be no surprise, knowing that GitHub is creating this editor.

---

<p>
    <img src="/static/resources/img/static/editors/2.png" class="editor-badge"/>
</p>

---

## File tree

You might find it odd I list the file tree as a category. 
From experience though, I feel the tree is one of the most important features which can really work with or work against you. 
You might not use the file tree at all, but a lot of people do. 
So I felt it was right to talk about it here.

**Sublime Text** is fast and this is also reflected in the tree. 
It lacks however some important functionality related to file manipulation from the tree.

**Brackets** has a very bulky and slow tree. Opening folders and files takes a notable time. 
It also offers only the bare minimal tools like Sublime: 
new files and folders, renaming, deleting and revealing/searching files.

**Visual Studio Code** doesn’t have a lot more tools than Brackets or Sublime, 
but it allows you to move files inside the tree, which is a big help. 
There are some minor points though. Visual Studio Code doesn’t show tabs, but uses the tree pane to show open files. 
It makes this pane become cluttered and makes it difficult to find the open file you’re looking for. 
It’s also not possible to scroll sideways. 
But you can use the same pane as a search and debugger view, which is space efficient.

**Atom** has a lot of tree functionality: there are simple tools like copy/paste, 
but also cut, duplicate, rename etc. 
You can also move files by dragging them. 
Atom furthermore integrates GIT project status in the file tree. 
The tree might feel a bit slower than Sublime or Visual Studio Code though.

Both Atom and Sublime have great file tree features, and both lack some. 
Sublime can’t be beaten by speed, but Atom offers a lot more functionality. 
Many people don’t use the tree view in Sublime, 
but together with Atom’s GIT status you’ll get a good project overview by just looking at the tree.

---

<p>
    <img src="/static/resources/img/static/editors/3.png" class="editor-badge"/>
</p>

---

## Performance

Performance is one of the most important metrics. 
All of these editors are performant for sure, but each has its own small differences.

**Atom** lacks in this category. 
There are two major issues: startup time and big files. 
Atom is built upon web technologies (HTML, CSS and JavaScript). 
It has some major advantages, but takes a while longer to load. 
It’s however only the startup, and still considerably faster than any IDE. 
Once everything is loaded, Atom is as fast as Brackets. On the other side, big file loading time is a disaster. 
Atom will open files once you’ve selected them in the tree view. 
It’s easy to miss click a minified file, which will make Atom hang for several seconds or even minutes. 

**Visual Studio Code** is a bit faster than Atom and Brackets,
it works as you might expect from a Microsoft product: not slow, but also not the fastest.

**Brackets** is comparable to Atom, but the slow and bulky tree view makes everything feel slower.

**Sublime** is by far the winner here. 
It’s lightning fast all the time, and can’t be beaten by any other editor. 
Atom and Brackets loose this competition, but are still a lot faster than full blown IDEs. 
Another aspect to keep in mind is the amount of packages you’re using. 
Atom actually tells you how much milliseconds each package adds to startup time. 
Sublime is also subject to this: the more packages the slower. 
But without any doubt: Sublime shines in the field of performance.

---

<p>
    <img src="/static/resources/img/static/editors/4.png" class="editor-badge"/>
</p>

---

## Configuration

**Sublime**, **Brackets** and **Visual Studio Code** offer an easy JSON config file for settings and keybindings. 
Brackets and Visual Studio Code even open a two column layout when editing settings, one with the defaults and one with your own. 
A small but convenient feature.

**Atom** however excels at customisability with its own stylesheet and startup script which can be hacked in any way you want. 
It has a built-in keybinding debugger, the Chrome developer tools, works with CoffeeScript (JS) and CSS. 
You don’t need to learn another language to customise Atom, it’s built upon web technologies.
Furthermore, each package has its own configuration page with a lot of documentation and sometimes input fields to set parameters.

---

<p>
    <img src="/static/resources/img/static/editors/5.png" class="editor-badge"/>
</p>

---

That was a lot of information! Some of the most important things summarized:

**Visual Studio Code** is focused on Node and ASP.NET development. 
It isn’t very customisable but has the Microsoft IDE feel to it. 
It’s an easy plug and play setup. 
Files are not shown in tabs, which makes it feel a bit unorganised, 
but I think that this is a preference and a developer can get used to this method of work.

**Sublime Text** has a lot of power. It’s fast and reliable. 
There are a lot of packages to customise your development environment, 
but they are often not very well documented. Sublime starts out as a text editor, 
but can be made the perfect, performant IDE with time and effort.

**Brackets** has some awesome front-end web development features like live previews, linters and PSD integration. 
The main downside is that it feels a bit slow, especially the file tree.

**Atom** is built on web technologies and its packages. 
It’s offers a very nice interface for packages and configuration and is “hackable to the core”. 
It has some quirks still with performance, but there’s a very active community working on it. 
Its customisability makes Atom accessible for a wide variety of programmers with their own workflow.

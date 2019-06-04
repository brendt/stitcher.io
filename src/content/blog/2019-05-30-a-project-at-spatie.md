The month May marks the first year anniversary of a client project I've been working on at Spatie.
I thought it useful to share some statistics with the community,
and give you a feeling of what a "real life web project" might look like.

Let's start with a general overview.
The project, a web application, features an admin interface to manage inventories, contacts and contracts;
bookings, automatic invoicing and about ten third party integrations.

In the future we'll be exposing several of these features to the outside via an API,
its main goal to power a mobile app for clients of the platform.
The admin panel is already in use in production.

The project is built in Laravel, a ((PHP)) framework.
We use Blade as a templating engine in combination with many VueJS components.
Tailwind is the ((CSS)) framework used.

## Some numbers

So, how much code have we written the past year? Here's a summary, 
gathered with the [phploc](*https://github.com/sebastianbergmann/phploc) package:

- ((#2,062#)) files
- ((#126,736#)) lines of code
- ((#97,423#)) logical lines of code

Let's zoom into statistics about the backend code, my area:

- ((#1,086#)) classes; ((#32#)) interfaces; ((#28#)) traits
- Average ((LLOC)) per class: ((#8#))
- Maximum ((LLOC)) per class: ((#139#))
- ((#3,245#)) public methods

The amount of lines split per file type looks like this:

![](/resources/img/blog/project-stats/loc.png) 

Let's further dive into how the backend code is structured, 
by using Stefan's [laravel-stats](*https://github.com/stefanzweifel/laravel-stats) package.

To start with, I should explain something about our big Laravel projects. 
Instead of using the default Laravel project structure, our code is split into two namespaces: 
"application code" and "domain code". 

Domain code holds all business logic and is used by the application layer. 
If you want to dive further into this topic, you can read about it [here](*/blog/organise-by-domain).

The following graph shows how application and domain code relate to each other:

![](/resources/img/blog/project-stats/domain-v-application.png)
 
By splitting business and application code, 
we're able to provide a flexible, maintainable and highly testable core.
Application code makes use of this core and looks very much like your average Laravel project. 
 
 The bulk of our domain code consists of three types of classes:
 
 - Models — ((#80#)) classes
 - Actions — ((#205#)) classes
 - ((DTO))s — ((#63#)) classes
 
 While the application layer mostly consists of:

- Controllers — ((#130#)) classes and ((#309#)) routes
- ViewModels — ((#82#)) classes
- Blade views — ((#313#)) files; these are not included in the chart above

Because of the lifecycle of the project, there's room for improvement. 
For example, we're not using [((DTO))s](*https://stitcher.io/blog/structuring-unstructured-data) everywhere, 
as they were added at a later time.

As with all things: we learn as we go. 
After a year, it's normal that some parts of the code can be considered "old".
We have a rule that states that when we work in these old parts of the codebase, we refactor them.

A big advantage of moving code into domains is testability. 
While our domain code is heavily unit tested, our application code is mostly only integration tested. 
In our experience, it's a workable balance between highly tested code and being able to meet deadlines. 

At the moment we have ((#840#)) tests doing ((#1,728#)) assertions. 
Our test suite could always be improved, 
but I am very confident deploying new features and refactors without the fear of breaking stuff — thanks to our test suite.

## Code structure

I'm a big proponent of clean code. 
We try to keep our code clean and maintainable, by setting a few rules of thumb:

- Classes should be small, 50 lines of code should be the maximum
- Methods should also be small and easy to reason about
- We prefer clear names over short and cryptic names

You probably noticed that we don't always keep these rules. 
There are some classes that are longer and more complex. 

These classes are the result of making choices: 
sometimes some technical debt is allowed to meet deadlines — as long as we're aware of it.

I've made a [little tool](*https://github.com/spatie/code-outliner) in the past which I use to generate "heat maps" of the codebase.
It will take all code in a folder, and generate an image by overlaying the code structure on top of it.

I can use this tool to locate large files, and refactor them when there's time. 
We have done this in the past, and it works very well.

Here's part of this image of a subdomain in our project:

![](/resources/img/blog/project-stats/outline.png) 

The darker the image, the more code across all files in that position.
You can see that while some files are longer, most of the code lives in the upper 50 lines, 
something we strive for.

We ensure these short classes and consistent code by using a few tools and methods:

- Internal ((PR))s and code reviews; despite what you might think, this saves time
- We use static analysis, more specifically [PhpStan](*https://github.com/phpstan/phpstan); 
to prevent subtle bugs 
- We use [((PHP CS)) fixer](*https://github.com/FriendsOfPHP/PHP-CS-Fixer) to ensure consistent code style

Like I said before: I'm a firm proponent of clean code. 
When you're working with several people in the same codebase, 
it's a must to keep your code clean and clear, to secure its future. 

## In closing

Finally, I'd like to show the ((GIT)) history of the project visualised with [Gource](*https://gource.io/).
We've been working on this project with, in total, ((#7#)) contributors, and now have more than ((#4,000#)) commits listed.

<p>
    <iframe width="560" height="315" src="https://www.youtube.com/embed/KkgAnOklQ7w" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
</p>

You can clearly see the different "branches" I talked about earlier: application- and domain code; 
but this overview also includes Blade, JavaScript and ((CSS)) files.

---

So what about your projects? Are you able to share your own stats? 
Feel free to send me a [tweet](*https://twitter.com/brendt_gd) or an [email](mailto:brendt@stitcher.io)!

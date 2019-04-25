Scopes in PHPStorm can significantly improve your development experience. 
They enable better search and allow for custom file colours. 

For example, this is what I'm talking about:

![A tree view configured with coloured scopes](/resources/img/blog/phpstorm-coloured-scopes/tree-view.png)

These colours allow you to easily recognise files,
and that in turn allows you to think more freely about things that really matter when coding.

First you'll want to configure one or more scopes. 
A scope is a set of textual filters that are applied on your files.

![Configuring scopes 1](/resources/img/blog/phpstorm-coloured-scopes/scope-configuration.png)

You can use the buttons to include and exclude folders and files, 
or you can write the filters yourself.
There's a special syntax, described [here](*https://www.jetbrains.com/help/phpstorm/scope-language-syntax-reference.html).

Don't forget you can expand the text area for easier configuration.

![Configuring scopes 2](/resources/img/blog/phpstorm-coloured-scopes/scope-configuration-extended.png)

## File colours

Every scope can be applied a specific colour. 
This makes it easy to easily spot files. 

![File colours](/resources/img/blog/phpstorm-coloured-scopes/file-colours.png)

By applying colours to a scope, you'll see them in the tree view, 
in file tabs and when using file navigation.

![File colours](/resources/img/blog/phpstorm-coloured-scopes/tab-colours.png)

## Filtering by scope

Besides colours, scopes also allow for easy filtering. For example, in the tree view.

![File colours](/resources/img/blog/phpstorm-coloured-scopes/tree-filter.png)

But also in the finder.

![File colours](/resources/img/blog/phpstorm-coloured-scopes/finder.png)

{{ ad:google }}

## Defaults

Setting up scopes shouldn't take longer than 10 minutes every project, 
and saves a lot of time in the long run. 

PHPStorm also offers a default settings option though, 
which will be used every every time you create a new project.
Go to `File > Default Settings` and configure your default scopes and colours over there.

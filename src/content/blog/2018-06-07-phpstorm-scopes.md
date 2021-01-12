Any JetBrains IDE has an amazing feature that can significantly improve your development experience; whether it's PhpStorm, WebStorm, IntelliJ IDEA, PyCharm, or any other project; this feature enables better search and allows for custom file colours. 

{{ ad:carbon }}

For example, this is what I'm talking about:

![A tree view configured with coloured scopes](/resources/img/blog/phpstorm-coloured-scopes/tree-view.png)

These colours allow you to easily recognise files,
and that in turn allow you to think more freely about things that really matter when coding.
First you'll want to configure one or more scopes. 
A scope is a set of textual filters that are applied on your files, you can configure them by going to `Settings > Scopes`.

![](/resources/img/blog/phpstorm-coloured-scopes/scope-configuration.png)

You can use the buttons to include and exclude folders and files, 
or you can write the filters yourself.
There's a special syntax to do that, you can read about it described [here](*https://www.jetbrains.com/help/phpstorm/scope-language-syntax-reference.html). Don't forget you can expand the text area for easier configuration:

![](/resources/img/blog/phpstorm-coloured-scopes/scope-configuration-extended.png)

## File colours

Every scope can be applied a specific colour. 
This makes it easy to easily spot files. 

![](/resources/img/blog/phpstorm-coloured-scopes/file-colours.png)

By applying colours to a scope, you'll see them in the tree view, 
in file tabs and when using file navigation.

![](/resources/img/blog/phpstorm-coloured-scopes/tab-colours.png)

{{ cta:dynamic }}

## Filtering by scope

Besides colours, scopes also allow for easy filtering. For example, in the tree view:

![File colours](/resources/img/blog/phpstorm-coloured-scopes/tree-filter.png)

But also in the finder:

![File colours](/resources/img/blog/phpstorm-coloured-scopes/finder.png)

## Defaults

Setting up scopes shouldn't take longer than 10 minutes every project, 
and saves a lot of time in the long run. 
There's also the possibility to set default options though, 
which will be used every every time you create a new project.
Go to `File > New Project Settings > Preference for New Projects` and configure your default scopes and colours over there, the same way you'd do as explained before.

And just in case you'd need some inspiration, these are my default scopes:

```
<hljs blue>App</hljs>
file:app//*||file:config//*||file:routes//*||file:app||file:config||file:routes||file:src//*||file:src

<hljs purple>Resources</hljs>
file:resources//*||file:resources

<hljs yellow>Database</hljs>
file:database//*||file:database
```

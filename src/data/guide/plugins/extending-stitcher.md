Stitcher offers a very simple, yet very powerful plugin system. Plugins are little programs added to Stitcher, so you'll 
 need to understand PHP. Furthermore, knowledge of [the Symfony Dependency Injection Component](#) is also required.
   
### What is a plugin

A Stitcher Plugin can add behaviour, or modify existing behaviour of the Stitcher core. A few examples:

- Custom adapters to extends page compiling behaviour.
- Another template engine besides Smarty or Twig.
- Extra variable parsers to support more data types.
- New commands to add console functionality.
- Whole applications like exposing Stitcher via a REST API.

When writing plugins, it's recommended to follow the existing conventions used in the core. Take a look at the
 [source code](https://github.com/brendt/stitcher) to get familiar with the core. 
 
Stitcher is built on top of Symfony components and PSR guidelines. So everything you need to know about a clean and good
 codebase can be found within those communities.
 
### Stitcher's internals

On the rest of this page, you'll find a summary of Stitcher's internal concepts. These can help you hook into the right classes.
 
#### Services and config

Stitcher is built on top of Symfony's service container. Stitcher bootstrap phase is done in the `Brendt\Stitcher\App`
 class. This class will load config files and build the container, including plugins.
 
#### Adapters

Adapter classes live in the `Brendt\Stitcher\Adapter` namespace, and can dynamically be added to the adapter factory: 
 `Brendt\Stitcher\Factory\AdapterFactory`.

#### Parsers

Variable parsers, or parsers for short, are used to parse data types into real values.

#### Application and commands

Stitcher adds a few different applications out of the box: the console application and the developer controller. Commands
 are added via the service container to the `Console` application.
 
#### Events

Stitcher has built in event support, using the Symfony's Event Manager. There aren't a lot of registered events yet though,
 this will be improved in the future. Events can be used to hook into the running flow of a Stitcher application.
 
#### Template

The `Brendt\Stitcher\Template` namespace holds first of all the `TemplatePlugin` class, which adds all kind of functionality
 to the template engines. It's one of the oldest parts of Stitcher though, and could use some love. You'll also find the 
 Smarty and Twig implementations in this namespace.
 
#### Factory

Stitcher uses a few factories to get the right things, eg. adapters or parsers. These factories can be extended by added your 
 own logic to them.

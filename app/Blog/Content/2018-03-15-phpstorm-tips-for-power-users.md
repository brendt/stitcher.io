---
type: blog
title: 'PHPStorm tips for power users'
highlight: false
next: phpstorm-scopes
meta:
    description: 'A selection of less-known-yet-powerful features of PHPStorm.'
---

A selection of less-known-yet-powerful features of PHPStorm.

{{ ad:carbon }}

## Pane modes

Every pane in PHPStorm has several modes and can be configured either by hand or via key bindings.

- `docked`: makes a pane not overlap with other panes or the code screen.
- `pinned`: automatically hides a pane when not pinned.
- `floating`: makes the pane float.
- `windowed`: makes the pane a full-blown window.
- `split`: to allow multiple panes in one area.

Working with non-pinned panes will allow for a much cleaner editor view. 
Binding certain panes to a key combination will show them at will.

<p>
    <img src="/img/static/phpstorm-power-users/panes.gif"/>
</p>

## Auto-imports

By default, PHPStorm will only auto-import namespaces if you're already in a namespaced file.
Auto imports can be configured to also work in normal PHP files 
in `Settings > Editor > General > Auto Import`.

## Code templates

You can change almost every template of auto-generated code in `Settings > Editor > File and Code Templates`
For example: generate getters and setters without docblocks, generate test functions in another format and others.

![-](/img/blog/phpstorm-power-users/code-templates.png)

## String actions

When pressing `alt + enter` (`Show Intention Actions`) on a string, you'll get multiple useful actions.
Things like `replace quotes` to toggle between single- and double quotes, 
`split string` to split the string, and more.

<p>
    <img src="/img/static/phpstorm-power-users/string-actions.gif"/>
</p>

## Copy paths

Two very useful commands:

- `Copy Paths` to copy the full path to the current file.
- `Copy Reference` to copy the relative project path and line number to the current file.

This "current file" can be the file you're editing, 
but could also be the selected file in the tree view or navigation bar.

## Commands to toggle options

Instead of opening the settings to toggle options, 
there are a lot of toggles you can manage from the command palette. 
For example: show or hide the tabs bar.

<p>
    <img src="/img/static/phpstorm-power-users/tab-placement.gif"/>
</p>

You can open the command palette with `⌘ ⇧ A` on the default Mac keymap. 
If you want to lookup the keybinding on your system: the command is called `Find Action`.

## Custom JVM options

PHPStorm runs on Java, and there's a file in which you can specify extra options for the JVM
to optimise performance. I've written about those options [here](/blog/phpstorm-performance).

## Distraction free mode

Distraction free mode will hide all panes by default, 
but you can easily bring them back via the command palette or key bindings.

Besides this "no clutter by default", your code will also align more centered, which can be a much more pleasant reading experience. 
The width of this centered code view is configured in `Settings > Editor > Code Style > Hard wrap at`.

<p>
    <img src="/img/static/phpstorm-power-users/distraction-free.gif"/>
</p>



## Color inspection

Do you want to know why a word is highlighted or change the colouring?
There's a command called `Jump to Colors and Fonts` which will allow you to edit 
the color of your current scheme, for that entry.

<p>
    <img src="/img/static/phpstorm-power-users/colors-and-fonts.gif"/>
</p>

## Any more suggestions?

I'd love to hear your own tips on how to use PHPStorm. 
Feel free to let me know via [Twitter](*https://twitter.com/brendt_gd) or [e-mail](mailto:brendt@stitcher.io).

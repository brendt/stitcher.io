*This blog post aims to make you think about the way you use key bindings whilst programming. 
You'll read about some techniques I use to assign key bindings, how to memorise them, and use them efficiently.
But before we go on, I'll need to explain why spending time on key bindings is worth the effort.*
  
## The need for keys

I can't point to some psychological study to back this claim, my own experience and common sense. 
 Using the mouse as less as possible when coding is a good thing. 
 You're not moving your hands around to grab the mouse, which saves time. 
 Also you don't have to make the mental switch between using a keyboard and a mouse as input device.
 
I believe these small things have to power to improve our skills as professional programmers significantly.
 I've experienced a lot of gain by taking the time to learn to use the keyboard as often as I can.
 While I'm still searching the optimal setup, I can already share some thoughts and techniques.
 The most important thing to know is that key bindings are a matter of personal taste. 
 So don't take these next points as law, but rather apply them to your own situation. 

> Key bindings are a personal preference. 

## The meaning of modifiers

A keyboard has a few modifier keys, which allow you to modify the behaviour of other key presses.
 A long time ago these keys were actually hard wired in the keyboard, to change the electronic bits sent to the computer.
 In this modern time, it's still good to look at what their original meaning was.
 It helped me define a formal definition for each modifier key, allowing me to remember what key combination belongs to which action.
 
 > Define a personal meaning for each modifier key, 
 <br>and stick to it.

### Meta/Command (⌘) 

I use this key when "executing" commands. Basically most of what's possible through the menu of an application. 

### Option/Alt (⌥) 

Alt stands for "alternate", changing the behaviour of another key combination. I use this key for a related action 
 of another key binding. 

### Shift (⇧) 

Shift has a double meaning. First it's used for selections, because that's default OS behaviour.
Second, it's also often used to reverse the action.

I prefer a maximum of two modifier keys, and if complexer combinations are needed, opt for **double key bindings**. 
One exception though: Shift (⇧) may be used in combination with other modifier keys to reverse the action.

> Prefer at most two modifier keys, 
<br>or use double key bindings.

### Control/Ctrl (^) 

I use the control key for text- and code related manipulations. 
 Actions like moving the cursor, working with selections, working with lines, etc. 
 I find it hard to give a formal definition for the Control key, but its use is clear in most cases. 

A note for Windows users: the Control key is used much more in comparison to the Meta (Windows) key. 
 Meaning you probably want to switch the definition of the two, or even ditch the Meta key.
 Even though this might seem like a good idea, adding the meta key in your workflow can be a good thing,
 as it adds another modifier key to your availability.

### Function (fn)

Because the function key is often not accessible on desktop keyboards, I choose not to depend on this key.
 I only make an exception for some edge cases like page-up or page-down. 

## Learning

Keeping my own definitions in mind, it's easy to start defining key bindings. Though to remember them requires practice.
 I'd recommend not assigning all key bindings at once, but rather slowly add them when you need them.

> Assign new key bindings when you need them.

I choose not to override operating system (OS) key bindings. Things like `copy`, `paste`, `select all` or `quit` are 
 never overridden.
 Key binding defaults provided by your IDE or editor, however, may be changed. 
 If you come from Sublime Text like me, you've probably learned some defaults which you are accustomed with. 
 When switching to PHPStorm a few years ago, I decided to keep some of those key bindings I knew from Sublime.
 
> There's no need to change OS-level key bindings 
<br>like `copy` or `select all`.
 
Even now, I'm still changing key bindings from time to time. Especially when I came up with my definition list.
 One thing I find useful when learning new key bindings, is to disable the old ones. IDEs like PHPStorm allow you to add
 multiple combinations for the same action. I prefer to immediately notice when I'm using an old combination.
 This makes me learn faster.
 
> Remove key bindings you wish to unlearn.
 
Furthermore, when stuck in a situation, I try not to immediately grab the mouse. 
 I try to think the problem and define what I want to do.
 Most of the time, I can remember which combination of keys should be pressed, because of the definition list above.
 When my memory fails me, I'm lucky to be working in an IDE with awesome key binding management, 
 so it's easy to find the correct combination back.

> Don't grab the mouse when panicking.

You keymap is a very personal file, which slowly grows to match your workflow the best. I recommend you storing a backup
 of your keymap somewhere else, GitHub would be a good place. 
 [Here's mine](*https://github.com/brendt/settings-repository/blob/master/keymaps/Brendt.xml).
 
> Check your keymap into version control.

---

### A few of my own examples

- `⌘ p` Search file
- `⌘ ⇧ p` Search recent files
- `⌘ ⌥ p` Search symbols in file
- `⌘ ⌥ space` Show suggestions
- `⌘ ⌥ enter` Go to declaration
- `^ ⌥ →` Move right with camelHops
- `^ ⌥ ←` Move left with camelHops
- `⌥ ↑`  Move cursor paragraph up
- `⌥ ↓`  Move cursor paragraph down
- `⇧ ⌥ ↑` Extend selection
- `⇧ ⌥ ↓` Shrink selection

## Closing thoughts

I grew in love with key bindings over the years. I still use the mouse for basic navigation, 
 but once I start coding, I try to use it as little as possible. I find that it's easier to work this way. 
 Not only do I gain time by not switching as often to the mouse; I also find it puts less cognitive load on my brain,
 meaning I'm able to concentrate more on coding.
 
This might seem like a small thing to do, but as a professional programmer, you're doing those small things many, 
 many times a day. It's worth taking the time to optimise these areas and skills, I find they make me a better programmer.
 
Do you want to read more about cognitive load? I've written about [fonts and visuals](*https://www.stitcher.io/blog/a-programmers-cognitive-load)
 in a previous blog post. Do you still have a question or something on your mind? [Send me an email](mailto:brendt@stitcher.io)!

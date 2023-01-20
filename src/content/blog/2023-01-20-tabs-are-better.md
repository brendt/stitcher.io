<p><iframe width="560" height="422" src="https://www.youtube.com/embed/w5sPf2fhnxE" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></p>

Do you want to know something dangerous?

Habits.

Not the kind where you practise long and hard to make something a habit. No, I’m talking about the kind where you’ve taught yourself something without noticing. The kind of habit that has been “there” for years, but you can’t remember how it came to be.

I’m not impeccable myself. I have several such habits, and I even have a couple that I know are wrong and should be changed.

For example: I use spaces instead of tabs for indentation.

We could talk about preference all day, but in the end we should ask: what are the rational arguments for tabs and spaces?

For tabs, there’s one big argument: accessibility. First and foremost, the size of a tab is configurable, it doesn’t have to be a fixed width of x spaces. Say there are two visually impaired people working on the same codebase. One person has to use a really large font size, so their tab length should be shorter than the default; and the other one prefers much larger tabs on a very wide monitor so that the difference between nested levels of code is more clear. Just like colour schemes or fonts, each developer can choose what style fits them best.

On top of that: blind people also code. They often use braille displays. These displays show a fixed number of characters, and every space wastes one. Say you’ve got 4 spaces per indentation level, and want to read something that’s nested 3 levels deep; then we’re talking about 12 braille characters being wasted on an already limited display. Compare that to only three when you’d use tabs.

But let’s not jump to early conclusions and look at the rational arguments for using spaces as well: Someone decided it would be the best. That’s it. “Consistency” is often coined as an argument because different editors might show different tab lengths, and some programmers… don’t like that?

A huge part of the programming community — including myself — has gotten used to spaces. Not because there are any good arguments for them, but because it has grown into a habit — a habit so big that popular code styles force users to write spaces, without any good reason.

So maybe we should rethink that habit? I would say that accessibility and writing more inclusive code is a very good argument. But unfortunately, as we all know: breaking habits is hard.

As a programmer I think I should always critically look at my own toolset and try to optimise it, regardless of my own subjective preference. It's by doing so that I've come to the conclusion that light colour schemes are better than dark ones, and today I want to share those thoughts with you. 
 
 Before looking at theory, grab a pair of sunglasses if you have any laying around. With both eyes open, cover only _one_ eye with one of the sunglass glasses. Make it so you're looking through your sunglasses with one eye, and use the other one like you're used to. 

With that setup in place, have fun watching this video in 3D!

<iframe width="560" height="400" src="https://www.youtube.com/embed/IZdWlXjhMo4" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Did you see the 3D effect? It might not work as well for parts of the video and some of you might not notice the 3D effect at all. That's fine, this post isn't about 3D, but it _is_ about the reason why you can watch that video in 3D with only a pair of sunglasses: the Pulfrich effect.

<div class="sidenote">    
<h2>The Pulfrich effect</h2>

The Pulfrich effect is a psychophysical percept wherein lateral motion of an object in the field of view is interpreted by the visual cortex as having a depth component, due to a relative difference in signal timings between the two eyes.
</div>

All sources are listed in the footnotes by the way, you'll find them at the end of this post.

To clarify, the 3D effect in the video is indeed your brain tricking you; it thinks there's depth in a moving flat image because there's a slight difference in timing between your left and right eye.
What's interesting though is what causes that timing difference. Can you guess? It's because you covered one eye with sunglasses, making the image darker. It turns out that dark images take longer to process than light ones.

<div class="sidenote">  

The Pulfrich effect […] yields about a 15 ms delay for a factor of ten difference in average retinal illuminance
</div>

By only covering one eye with sunglasses, you add a few milliseconds of delay to that one. The exact delay will depend on the brightness of the screen and the darkness of the sunglasses, which might explain why some people see the 3D effect better than others. The timing difference between your eyes causes your brain to interpret that image as having depth, hence 3D. 

{{ ad:carbon }}

Now on to programming. If you're using a dark colour scheme, you're deliberately adding extra delay, so says the Pulfirch effect. 
Sure the difference seems negligible, it's only a few milliseconds. Actually, it's a few milliseconds _every_ time you "rescan" your screen; that's between 10 or 50 times a second, depending on what research you want to believe.
Still you probably won't notice any real-time difference, but over time this adds up, and the extra effort needed by your eyes can become to feel exhausting.

Besides the Pulfrich effect, there are other reasons that make light colour schemes superior. First of there's what human eyes are used to, what they are built for. Most of us are awake during the day and asleep at night. The human eye is better adapted to interpreting light scenes with dark points of focus, instead of the other way around.

On the other hand there's the case of astigmatism, which is caused by an imperfection of your corneas or lenses. It's estimated that between 30% and 60% of adults in Europe and Asia have it (I actually have it myself, which is why I wear glasses). For people with astigmatism, a bright display with dark text is easier to read, because the iris closes a little more given the additional light; which decreases the impact of the defect in your cornea or lens.

As a sidenote: if you often experience headaches after a day of programming, you might want to test for astigmatism. Glasses make a world of difference

Lastly, there have been extensive studies about the readability of computer screens, one example is a study by Etienne Grandjean, called "Ergonomic Aspects of Visual Display Terminals". You can't read it online; if you manage to find it in a library you should check out pages 137-142. Its conclusion, like several other studies is that it's indeed easier to read dark text on a light background, then the other way around.

Often when I share these arguments with someone who clings to the dark side, they tell me light colour schemes hurt their eyes because they are too bright; you might be thinking the same right now. I've got two answers for you.

First: you don't need to use a white `#fff` background with black `#000` text. There's lots of light colour schemes that don't go to the extreme ends. The important thing is that there's enough contrast between fore- and background, and that the background is lighter than the foreground.
Second: you can always adjust the brightness of your screen. You don't need to turn it up to a 100%! You'd only do that if the text is otherwise unreadable, and guess when that happens? If you'd use a dark scheme!

---

I don't want to end with theory though. Over the past three years, I've put light themes to the test: I've challenged myself and dozens of others to switch to a light theme for **one week**. I wanna do the same with you: try it for one week, and let me know whether you're switching back to a dark theme or not. Based on my past experiments I can tell you that only a few people decide to switch back. The majority stays with a light scheme because, guess what, it's actually better.

Now I reckon there _are_ people who can't use a light colour scheme because of an eye illness. There are legitimate cases when dark colour schemes _are_ better for some people's health, the exceptions to the rule. 

So try it out, and let me know your findings via [Twitter](*https://twitter.com/brendt_gd) or [e-mail](mailto:brendt@stitcher.io)!

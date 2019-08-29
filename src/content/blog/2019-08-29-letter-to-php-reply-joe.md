Thanks you Joe for taking the time to [reply](*https://blog.krakjoe.ninja/2019/08/bearings.html) to [my letter](*/blog/a-letter-to-the-php-team), I really appreciate it! I'll be happy to reply here.

Your reply started by addressing the P++ shenanigans:

> A lot of the discussion is based on an assertion made during the P++ discussion that there are two camps of developers

As a matter of fact, I [started writing](*https://github.com/brendt/stitcher.io/commit/26fc4de353ca6beaff1cfd7b5f4b0c86f4f739b6) my letter before the whole P++ galaxy discussion. Life got in the way though (my son was born at the beginning of August), which is why it took almost a month to publish it.

I just wanted to make clear that I already had many thoughts on the topic before P++ was ever mentioned.

> You can tell by looking at the history of RFCs that these factions do not in fact exist

Whether we call it factions or not, if you take a look at recent ((RFC))s, they have almost always gone off topic for to discuss the future of ((PHP)) on a broader scale. Sure there might only a few people, but loud voices are heard nevertheless.

A few examples:

- [Reclassifying engine warnings](*https://externals.io/message/106713)
- [Short open tags](*https://externals.io/message/106384)
- [Namespace-scoped declares](*https://externals.io/message/101323)
- [Explicit call-site send-by-ref syntax](*https://externals.io/message/101254)
- [Deprecations for 7.4](*https://externals.io/message/106012)

This is how most of these discussion go:

- Nikita tries to move the language forward
- Zeev and/or Stas advocate for backwards compatibility, resulting in the same long conversation over and over again
- Sara tries to keep the middle ground
- Dmitry is working on ((PHP 8)) somewhere in the background, and steers away from these discussions

> The rules were written many years ago - arguably for a totally different, pre social coding world - we mostly do a good job of following the rules as they are written.

I think you addressed the essence of the problem: the way ((PHP)) internals work is outdated and inefficient in these modern times. 

> It's important to point out that the rules are not exhaustive, a lot of how we behave is determined by convention. You can argue against this and say that we should try to exhaustively enumerate every possible action

I'd argue we need a sensible and modern day rule set, which can be flexible.

> Recently an RFC was conducted to deprecate and remove short PHP tag syntax

While I do have my opinions on maintaining backwards compatibility — which I addressed in the first part of my letter — I think the most important thing to take away from the short syntax ((RFC)) is that the process is clearly broken, and needs fixing.

> What we have here is a failing of our processes and nothing more. I and likely others are considering how we might avoid this very same failure in the future. It seems desirable at this time to introduce a formal deprecation policy, this both achieves the goal of avoiding this very same failure, and can potentially increase confidence when it comes to adopting new versions of PHP.
  
 Glad we're on the same page on this one, and I know from your comments on internals that you're also looking for a balanced solution. My question is whether this is possible within the current system. It feels like we're going around in circles and very little progress is made.

> First, for the sake of clarity. You must be careful how you determine something to be controversial. Loud, is not the same as controversial

That's true, though a few loud voices can impact the development of ((PHP)) significantly. There aren't many core contributors, and they have to spend a lot of time reading and replying through the same discussions. I try to keep up-to-date with the internals list myself, so I know this is an exhaustive task.

> The time and effort it takes to change our processes is considerable, and only becomes a priority when it's obvious that our processes are failing, or have the potential to fail and do damage.

I think ((PHP)) is only slowly evolving because there's so much needless discussions happening over and over again, I'd call that a failing process.

> I'm sure that you have a sample of data that shows you this, or you surely wouldn't have made this claim.

Yes, I linked some recent examples previously.

> It's a matter of fact that some people can't seem to behave themselves on the internet, while I'm sure (read: must believe) they are reasonable people in real life. These people make themselves obvious very quickly and prove they have nothing much to say.

I think some kind of moderation would be in place, as these people keep coming back, and there's no way to stop them. This is where a mailing list just doesn't suffice.

> I can't argue that mailing lists are a good way to communicate, but it's what we have.
> However, it's not all we have:

True, though the channels you list still don't seem to bridge the gap between core- and userland developers. This is evident by the amount of userland developers voicing their opinions on all kinds of social media. I think a public forum is the way to go here.

> This is regularly mentioned, and I think I'll be the first one to point out that to the extent which that is true, it is the communities fault.

There are two groups here:

- People who are very much invested in ((PHP)), but don't want anything to do with the internal discussions, because of offtopic and exhausting conversations
- People who would actually want to contribute in the form of votes and input, but don't know how.

The wiki isn't very clear on this:

> People with php.net VCS accounts that have contributed code to PHP
>
> Representatives from the PHP community, that will be chosen by those with php.net VCS accounts
> Lead developers of PHP based projects (frameworks, cms, tools, etc.)
> regular participant of internals discussions

Personally, I think of myself in this second group, though it's unclear to me whether my perception is correct. And I can think of several other developers who would be an accurate representation of the ((PHP)) community. 

Should I ask? As a matter of fact, I did ask a core member personally, but didn't get a reply. Should I ask this question on the internals list? I'm unsure.

This is again an example of failing communication between core- and userland developers. There's a barrier that's perceived as uncrossable. 

Now I'm not saying there is an actual barrier, I just say that it's perceived by many userland developers this way, myself included.

---

Once again, thank you very much for your reply Joe. I highly appreciate it and am looking forward to read your comments on mine.

Kind regards
<br>
Brent

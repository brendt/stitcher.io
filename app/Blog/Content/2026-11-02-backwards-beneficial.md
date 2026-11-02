---
title: Backwards beneficial
meta:
    description: Some thoughts on backporting recent PHP security fixes
---

I'm conflicted. 

PHP had one of the biggest security incidents in history two weeks ago. Of course, it's only guessing how many websites didn't bother to update, and we're likely only see the effect of those websites potentially being hacked months or years from now. Active PHP professionals were of course quick to upgrade, and soon the dust settled.

But yes, I am conflicted. Not because of the zombie websites out there — there's no saving them anyway — but because of the countless PHP projects running unsupported versions; anything lower than 8.2. This would have been the perfect moment to put in the work to get back up to date (and stay up to date for the years to come).

And that's what PHP itself is pushing for: they only make security releases for recent versions. However, soon after the patches were released, Ondřej Surý was able to backport them to older, unsupported versions; making it so that outdated projects could still benefit from the security release.

That in itself isn't a bad, by the way. I'm glad there were backports available so quickly, making sure as little websites as possible would be vulnerable to these newly discovered security issues.

But… it's not really a long-term solution; is it? This whole ordeal depended on one person deciding to backport security patches. That's a major bottleneck. On top of it, it gives a false sense of security to anyone running unsupported PHP versions. What will happen the next time a critical vulnerability is discovered? Will Ondřej be there again? What about the time after that one? And after that one?

You could argue that this would be a better job for the Foundation to take care of? And I would say: they already do! They support up to four years of PHP releases, that is plenty of time for any project to upgrade to the latest version. Even more so in this day and age of AI: getting your projects up-to-date has never been as trivial as it used to be.

But no, instead we've applied a band-aid to one wound, hoping by the time the next one comes around, Ondřej will be there once again.

What I want to say… Just update. Just do it. Do it now without time pressure, instead of postponing until the next time a critical vulnerability is disclosed right when you're on vacation or having a day off. 

Invest in certainty instead of gambling.
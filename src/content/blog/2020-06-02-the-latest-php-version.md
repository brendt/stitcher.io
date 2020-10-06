It seems like a simple question with a simple answer: the latest PHP version currently is [PHP 7.4](/blog/new-in-php-74). However! If you want to know more inside information about how PHP's lifecycle is managed, keep on reading!

## Levels of support

{{ ad:carbon }}

PHP versions are grouped in three levels of support: active, security fixes only and end of life. The "end of life" versions should be avoided at all costs, usually a version goes into the "end of life" phase after three years. A year before that, it goes into the "security fixes only" phase, where only the most needed security fixes are released, and no more other improvements or bug fixes.

These are the currently active supported PHP versions:

<table>
<tr>
    <td>PHP 7.2</td>
    <td>Security fixes until November 30, 2020</td>
</tr>
<tr>
    <td>PHP 7.3</td>
    <td>Active support until December 6, 2020</td>
</tr>
<tr>
    <td>PHP 7.4 (current)</td>
    <td>Active support until November 28, 2021</td>
</tr>
</table>

This release cycle guarantees that, usually, only three versions of PHP are supported at any given time, with only two being actively supported.
I say _usually_ because PHP 5.6 had a year extra of security fixes support, but that has also ended now: PHP 5.6 was supported up until December 31, 2018.

## A look at the past

So, if you're not running PHP 7.2, 7.3 or 7.4, you're running a version that won't receive any updates anymore. At least none from the official PHP developers. There are in fact companies still working independently on old PHP versions like PHP 5.6. 

These initiatives of course only exist because there's a need for them: PHP 5.6 is somewhat the Windows XP of the PHP world: it was such a popular release at the time, and many older projects aren't able to deal with the breaking changes the new major version of PHP brings. As a sidenote: the new PHP version after PHP 5.6 is PHP 7 and 6 was skipped; but that's a story for another day. 

The PHP 5.* era was the one that set PHP on a course for maturity, a path that would be continued in the 7.* versions, and one that has proven itself over the past years: PHP's performance increased significantly compared to the 5.* versions, the rich community only, and the language syntax and type system kept evolving towards a modern day language.

Obviously PHP bears the consequences of more than 26 years of legacy with it, but in that time it has also proven itself to be a robust and stable language, despite its reputation.

About that reputation — PHP is still looked down upon by many who don't know the modern language — PHP is quite a good language these days. Yes, it carries its battle scars, and we'd wish for many legacy things to just go away, but overall it's quite a nice tool; one that has proven itself time and time again.

These days, the core teams keeps a consistent release cycle: one new release every year, and every 4 or 5 years a new major release. And there are exiting times to come: 2020 will be the year of the new major release, since [PHP 8](/blog/new-in-php-8) is coming by the end of the year!

Every new release is done in stages: the last months before the general availability — GA, or simply _the release_ — is focused on testing all the new features and changes. First there are couple of alpha releases, followed by beta releases, followed by release candidates, followed by the final release: GA.

During the alpha phase, new features are still allowed to be added. But once in beta, the final form of the new version has been decided on. 

These features, by the way, are decided upon by a core committee who votes on RFCs, which stands for _request for comments_. These RFCs describe a feature or a change to the language, and are discussed in depth. Everyone is allowed to make RFCs if they want to, but RFCs are of course critically looked at.

## PHP 8 release schedule

<table>
<tr>
    <td>Alpha 1</td>
    <td>June 25, 2020</td>
</tr>
<tr>
    <td>Alpha 2</td>
    <td>July 9, 2020</td>
</tr>
<tr>
    <td>Alpha 3</td>
    <td>July 23, 2020</td>
</tr>
<tr>
    <td>Feature freeze</td>
    <td>August 04, 2020</td>
</tr>
<tr>
    <td>Beta 1</td>
    <td>August 06, 2020</td>
</tr>
<tr>
    <td>Beta 2</td>
    <td>August 20, 2020</td>
</tr>
<tr>
    <td>Beta 3</td>
    <td>September 3, 2020</td>
</tr>
<tr>
    <td>Beta 4</td>
    <td>September 17, 2020</td>
</tr>
<tr>
    <td>Release candidate 1</td>
    <td>October 1, 2020</td>
</tr>
<tr>
    <td>Release candidate 2</td>
    <td>October 15, 2020</td>
</tr>
<tr>
    <td>Release candidate 3</td>
    <td>October 29, 2020</td>
</tr>
<tr>
    <td>Release candidate 4</td>
    <td>November 12, 2020</td>
</tr>
<tr>
    <td>General availability</td>
    <td>November 26, 2020</td>
</tr>
</table>

It _is_ possible by the way, for the dates to shift a little bit still, this will depend on how testing goes early on. Speaking of testing: everyone is allowed to do so, it's even encouraged to try stuff out in your own projects, so that enough feedback can be provided to the core team on time.

{{ ad:front-line }}

---

I told you there was more to PHP's release cycle than just one active version 😁. My advice for modern-day projects would be this: always make sure to update at the latest a few months after GA. Don't lack behind 2 or 3 versions, or even worse: be stuck in PHP 5 land. 

The updates actually never were that much of a pain, and there's so much to gain: performance improvements, security benefits, new syntax, wider community support. So keep that in mind, and let's look forward to [PHP 8](/blog/new-in-php-8)!

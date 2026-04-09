---
title: More dependency considerations
---

Last Friday, I wrote a blog post about how [we should take better care of our project dependencies](/blog/dependency-hygiene). It got a lot of good reactions, and [many](https://github.com/N3XT0R/laravel-passport-modern-scopes/pull/2) [open](https://github.com/marcoshoya/MasterpassBundle/pull/9) [source](https://github.com/esensi/model/pull/50) [maintainers](https://github.com/esensi/model/pull/51) [seemed](https://github.com/RiverVanRain/indieweb/pull/1) [to](https://github.com/phlib/encrypt/pull/7) [appreciate](https://github.com/phpcfdi/image-captcha-resolver/pull/9) [I sent](https://github.com/phpDocumentor/Reflection/pull/735) [them](https://github.com/civicrm/civicrm-core/pull/35315) [a](https://github.com/irman/legacy-encrypter/pull/1) [PR](https://github.com/Soneso/stellar-php-sdk/pull/77) with redundant dependencies removed.

I did get one worrisome comment from the folks at Paragon; these are the people who make `paragonie/sodium_compat` and `paragonie/random_compat`. I told them I'd react to their comments, which is what I'm doing now. I'm writing it as a blog post, because I think they highlight a crucial point that needs talking about.

Setting the scene real quickly, though, if you haven't read the previous post: I scanned all dependendants of popular compat and polyfill packages and determined which of those projects have redundant dependencies on those polyfills (based on their PHP version). For example, a package requiring `paragonie/sodium_compat` but also PHP higher than 7.2, doesn't need that dependency anymore, because `ext-sodium` comes built-in with PHP as of 7.2. Out of ±1.5k open source packages scanned, around 200 had these unnecessary dependencies. The point I made in that previous blog post is that we should take better care of our dependencies, as each dependency might pose a potential security threat (see the recent NPM side-chain attacks).

So I notified all of these 200 packages about these redundant dependencies and sent them a PR with them removed. This is where Paragon comes in. They commented on a bunch of PRs, on Reddit, and my blog, I'll quote from their comment on the previous blog post:

> While removing random_compat from PHP 7+ projects makes perfect sense, sodium_compat is actually necessary for libraries whose users might install their code in a system without the ability to install PHP extensions from PECL and/or whose host doesn't install the php-sodium package (or equivalent).
> Just because ext-sodium is available for PHP 7.2+ doesn't mean everyone uses it.

I need to say thanks to Tim for pointing out an oversight on my part: even though the [PHP docs](https://www.php.net/manual/en/sodium.installation.php) say "As of PHP 7.2.0 this extension is bundled with PHP", they _also_ point out that on Linux systems, sodium is _not_ built-in with PHP.

> Unfortunately, your actions here will have more harm than good.
>
> Let's say acme/awesome-lib depended on paragonie/sodium_compat in all versions older than v1.45.3, but they removed it because of the pull request cut by your script, and v1.45.3 included the "fix". Then an unrelated RCE vulnerability was found and a fix was released in v1.45.4. Users without ext-sodium installed will remain vulnerable because of this change.

So what the folks at Paragon are saying: by removing the dependency on `paragonie/sodium_compat` in awesome-lib, some systems might not be able to update to newer versions of awesome-lib anymore. Let's explore that problem:

- Simply by removing the `paragonie/sodium_compat` in awesome-lib, dependants would still be able to update. They could run into errors when calling sodium functions, if they were running their code on a system without `ext-sodium`.
- Let's say we not only removed `paragonie/sodium_compat`, but also added a new requirement on `ext-sodium` in awesome-lib. This meant the new version might not be compatible anymore, and running a naive `composer up` would silently ignore it — which is the problem the Paragon folks were afraid about. For clarity: my PR did not add the additional `ext-sodium` requirement.

I'm sure `paragonie/sodium_compat` is a fine library; but in the spirit of dependency hygiene, I really think we should make an effort to use what comes with PHP itself. Most importantly, I would argue that it's never up to packages to require compat or polyfill libraries. These libraries are system-dependent and should be managed on the project/system level; not by a package that can be installed on _any_ system (and thus may not be relevant on many systems).
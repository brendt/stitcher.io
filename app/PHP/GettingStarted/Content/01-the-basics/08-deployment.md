---
title: Deployment
meta:
  title: How to deploy PHP to production in 2026
description: Learn about the different options to deploy your PHP website to production.
image: meta/php/08-deployment.png
---

PHP, being one of the OG languages for backend web development, has a variety of ways to deploy to production. In this chapter we'll first explore the basic architecture of PHP and how it relates to production deploys, and then discuss which options are out there.

## PHP's architecture

What makes PHP unique compared to many other programming languages is that it's designed to be stateless by default. An HTTP request comes in, PHP boots and handles it to create a response, and then it shuts down again; nothing is shared between requests. It makes PHP an especially good match for doing backend web development, since HTTP itself is designed to be stateless as well.

We're getting ahead of ourselves, but it's good to note that these days PHP can run in a stateful mode as well, thanks to several third-party options. We'll cover them later in this chapter. For now, it's best to say that PHP by default is stateless and does a "cold boot" for every request, similar to how serverless applications are written with JavaScript, for example.

It might sound wasteful to do a cold boot for every request — and it is — which is why PHP has a robust caching layer and process manager to circumvent performance issues. PHP's internal cache is called [OPCache](https://www.php.net/manual/en/book.opcache.php), and it should always be enabled in production projects. The idea is that OPCache will store precompiled PHP code and execute that, instead of having to compile textual PHP code time and time again. 

The second piece of the puzzle is [FPM](https://www.php.net/manual/en/install.fpm.php) — FastCGI Process Manager. FPM is a process that sits between your web server (nginx, Caddy, or Apache are common options) and will manage a pool of available PHP processes to handle requests. Think of it as an orchestrator which runs a bunch of PHP workers and balances requests between available workers.

## Web servers

Most popular web servers like nginx, Caddy, and Apache have support for PHP. If you're going the self-managed route where you maintain your own servers, it's important to know how these tools work under the hood. I find that these tutorials are great starting points:

- [How to install the LEMP stack](https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-in-ubuntu-16-04) by DigitalOcean
- [How to use Caddy with PHP](https://php.watch/articles/caddy-php) on PHP.watch
- [LAMP stack tutorial](https://www.digitalocean.com/community/tutorials/how-to-install-lamp-stack-on-ubuntu) by DigitalOcean
- [PHPDocker.io](https://phpdocker.io/) to configure Docker containers to run PHP

### Shared hosting

This is the cheapest and most limited option. Shared hosting tends to mean uploading files over FTP to a server you share with many other sites, often without SSH access, Composer, or a choice of PHP version. Most of what this chapter covers won't be available there.

### VPS

You rent a virtual machine by the month and set it up yourself, following tutorials like the ones above. Updates, backups and security are yours to handle.

- [Hetzner](https://www.hetzner.com/)
- [DigitalOcean](https://www.digitalocean.com/) - also cloud offerings

### Server management tools

These run on a VPS you rent yourself. You bring the server, the tool provisions it and handles deploys, SSL and cron. You keep root access, and pay for both the tool and the server.

- [Laravel Forge](https://laravel.com/forge)
- [ploi.io](https://ploi.io/)

### Managed cloud platforms

You push code and the platform builds and runs it. Scaling is a setting. You get no root access; the provider patches the platform.

- [Upsun](https://upsun.com/) — formerly Platform.sh, also sold as [Symfony Cloud](https://symfony.com/cloud/)
- [Laravel Cloud](https://laravel.com/cloud)
- [fortrabbit](https://www.fortrabbit.com/)
- [WPEngine](https://wpengine.com/) — WordPress only

### Serverless

PHP also integrates well with serverless platforms like AWS Lambda, where your code is invoked per request instead of running on a server that waits for one. In practice, the use cases are limited.

- [Bref](https://bref.sh/) runs PHP on AWS Lambda

### Others

PHP hosting comes in many flavors — [findhost.app](https://www.findhost.app/?runtimes=php) compares PHP hosts side by side.

## 12 factor design

Many cloud platforms expect applications to follow [twelve-factor](https://12factor.net/) principles, and the factor that catches people out first is the filesystem, which is usually _ephemeral_: every deploy ships a fresh copy of the application, and anything written to disk since the last one — user uploads, generated PDFs, thumbnails — goes with the old copy. Scaling past one instance has the same effect. Keep uploads out of the application directory and in object storage instead; [Flysystem](https://flysystem.thephpleague.com/) gives you one API across providers, and both Laravel and Symfony build on it.

## Stateful deployments

Even though PHP is stateless by design, a few tools keep long-running PHP processes alive across requests — worker mode, as it's usually called. _In theory_ that serves more requests by skipping the cold boot, but most bottlenecks aren't boot time, so don't expect an application to get faster just by switching.

- [FrankenPHP](https://frankenphp.dev/) is an app server written in Go. It includes Caddy so you don't have to set it up manually, and most popular frameworks have an integration.
- [Symfony's runtime component](https://symfony.com/doc/current/components/runtime.html) abstracts away the difference between stateful and stateless setups, running Symfony on FrankenPHP as well as [ReactPHP](https://reactphp.org/) and [OpenSwoole](https://openswoole.com/).
- [Laravel Octane](https://laravel.com/docs/13.x/octane) is Laravel's version, integrating with FrankenPHP, [Swoole](https://github.com/swoole/swoole-src), OpenSwoole and [RoadRunner](https://roadrunner.dev/).

## Closing thoughts

As you can see, there are lots of options out there. PHP has been around for more than 30 years and has been used for many different use cases. My advice would be to start simple with a dedicated server, either managed via a third-party platform or self-managed; you'll be surprised how fast PHP actually is when setup correctly. When the need is there, you can always opt for more complex solutions like serverless or stateful deployments — depending on your project's needs.

Nothing listed above is sponsored in any way; if you'd like to see another tool here, [let me know](mailto:brendt@stitcher.io).

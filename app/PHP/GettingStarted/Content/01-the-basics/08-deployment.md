---
title: Deployment
description: Learn about the different options to deploy your PHP website to productions.
image: meta/php/08-deployment.png
---

PHP being one of the OG languages for backend web development, has a variety of ways to deploy to production. In this chapter we'll first explore the basic architecture of PHP and how it relates to production deploys, and then discuss which options are out there.

## PHP's architecture

What makes PHP unique compared to many other programming languages is that it's designed to be stateless by default. An HTTP request comes in, PHP boots and handles it to create a response, and then it shuts down again; nothing is shared between requests. It makes PHP an especially good match for doing backend web development, since HTTP itself is designed to be stateless as well.

We're getting ahead of ourselves, but it's good to note that these days PHP can run in a stateful mode as well, thanks to several third-party options. We'll cover them later in this chapter. For now, it's best to say that PHP by defualt is stateless and does a "cold boot" for every request, similar to how serverless applications are written with JavaScript, for example.

It might sound wasteful to do a cold boot for every request — and it is — which is why PHP has a robust caching layer and process manager to circumvent performance issues. PHP's internal cache is called [OPCache](https://www.php.net/manual/en/book.opcache.php), and it should always be enabled in production projects. The idea is that OPCache will store precompiled PHP code and execute that, instead of having to compile textual PHP code time and time again. 

The second piece of the puzzle is [FPM](https://www.php.net/manual/en/install.fpm.php) — FastCGI Process Manager. FPM is a process that sits between your web server (nginx, Caddy, or Apache are common options) and will manage a pool of available PHP processes to handle requests. Think of it as an orchestrator which runs a bunch of PHP workers and balances requests between available workers.

## Web servers

Most popular web servers like nginx, Caddy, and Apache have support for PHP. If you're going the self-managed route where you maintain your own servers, it's important to know how these tools work under the hood. I find that these tutorials are great starting points:

- [How to install the LEMP stack](https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-in-ubuntu-16-04) by DigitalOcean
- [How to use Caddy with PHP](https://php.watch/articles/caddy-php) on PHP.watch
- [LAMP stack tutorial](https://www.digitalocean.com/community/tutorials/how-to-install-lamp-stack-on-ubuntu) by DigitalOcean
- [PHPDocker.io](https://phpdocker.io/) to configure Docker containers to run PHP

If you don't want to manage your own servers, then you could opt for cloud providers that manage and setup PHP for you. These are often paid platforms and not sponsored in any way, if you'd like to see another tool listed here, [let me know](mailto:brendt@stitcher.io).

- [Laravel Forge](https://laravel.com/forge)
- [Upsun](https://upsun.com/)
- [ploi.io](https://ploi.io/)
- [WPEngine](https://wpengine.com/)
- And many more, feel free to google to find whatever platforms fits your needs the best

These platforms take a classic approach where you have a dedicated server handling requests. PHP also integrates well with serverless platforms like AWS. Popular platforms are:

- [Bref](https://bref.sh/)
- [Symfony Cloud](https://symfony.com/cloud/)
- [Laravel Cloud](https://laravel.com/cloud)

## Stateful deployments

Even though PHP is stateless by design, these days there are a number of options to keep long-running PHP processes alive that serve multiple requests. The benefit of stateful deployments is that _in theory_ they can serve more requests because the cold-boot phase of PHP and whichever framework you're using is skipped. That being said, most performance bottlenecks don't come from boot time, and you cannot expect any application to become immediately faster just by running it in worker mode (which is what these long-running processes are called).

There are a number of tools and integrations out there. We'll list a few:

- [FrankenPHP](https://frankenphp.dev/) is an app server for PHP, written in Go. It includes Caddy so that you don't have to set it up manually and allows you to keep PHP processes running between requests. Most popular frameworks have integrations for FrankenPHP.
- [Symfony's runtime component](https://frankenphp.dev/) is designed to abstract away the different between stateful and stateless setups and allows Symfony to run with FrankenPHP, but also a number of other runners like [ReactPHP](https://reactphp.org/) and [OpenSwoole](https://openswoole.com/).
- [Laravel Octane](https://laravel.com/docs/13.x/octane) is Laravel's version that integrates with FrankenPHP, [Swoole](https://github.com/swoole/swoole-src), OpenSwoole, and [RoadRunner](https://roadrunner.dev/).

## Closing thoughts

As you can see, there are lots of options out there. PHP has been around for more than 30 years and has been used for many different use cases. My advice would be to start simple with a dedicated server, either managed via a third-party platform or self-managed; you'll be surprised how fast PHP actually is when setup correctly. When the need is there, you can always opt for more complex solutions like serverless or stateful deployments — depending on your project's needs.
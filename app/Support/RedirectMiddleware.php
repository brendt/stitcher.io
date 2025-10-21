<?php

namespace App\Support;

use Tempest\Core\Priority;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;

#[Priority(Priority::EXCEPTION_HANDLING)]
final class RedirectMiddleware implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $redirects = [
            '/timeline-taxi' => 'https://timeline-taxi.com/',
            '/discord' => 'https://discord.gg/pPhpTGUMPQ',
            '/blog' => '/',
            '/uses' => '/blog/uses',
            '/blog/the-latest-php-version' => '/blog/new-in-php-81',
            '/light' => 'https://www.youtube.com/watch?v=mu0HJ0_kprc',
            '/twitter' => 'https://twitter.com/brendt_gd',
            '/repot' => 'https://youtu.be/Swu2M1LL33c',
            '/the-road-to-php-81/subscribe' => 'https://road-to-php.com',
            '/the-road-to-php-81/pending' => 'https://road-to-php.com/success',
            '/the-road-to-php-81/unsub' => 'https://road-to-php.com/unsub',
            '/the-road-to-php-81/success' => 'https://road-to-php.com/success',
            '/blogs-for-devs' => '/blogs-for-devs/01-intro',
            '/feed' => '/rss',
            '/feed.xml' => '/rss',
            '/rss.xml' => '/rss',
            '/games' => '/games/all',
            '/dejob' => '/de-job',
            '/podcasts' => '/podcasts/all',
            '/blog/laravel-domains' => '/blog/organise-by-domain',
            '/blog/rethinking-with-events' => '/blog/an-event-driven-mindset',

            '/laravel-beyond-crud' => 'https://beyond-crud.stitcher.io',
            '/blog/laravel-beyond-crud' => 'https://beyond-crud.stitcher.io',
            '/blog/laravel-beyond-crud-is-here' => 'https://beyond-crud.stitcher.io',
            '/blog/laravel-beyond-crud-01-domain-oriented-laravel' => 'https://beyond-crud.stitcher.io',
            '/blog/laravel-beyond-crud-02-working-with-data' => 'https://beyond-crud.stitcher.io',
            '/blog/laravel-beyond-crud-03-actions' => 'https://beyond-crud.stitcher.io',
            '/blog/laravel-beyond-crud-04-models' => 'https://beyond-crud.stitcher.io',
            '/blog/laravel-beyond-crud-05-states' => 'https://beyond-crud.stitcher.io',
            '/blog/laravel-beyond-crud-06-managing-domains' => 'https://beyond-crud.stitcher.io',
            '/blog/laravel-beyond-crud-07-entering-the-application-layer' => 'https://beyond-crud.stitcher.io',
            '/blog/laravel-beyond-crud-08-view-models' => 'https://beyond-crud.stitcher.io',
            '/blog/laravel-beyond-crud-09-test-factories' => 'https://beyond-crud.stitcher.io',

            '/presentations' => 'https://github.com/brendt/presentations',
            '/slides' => 'https://github.com/brendt/presentations',
            '/guide' => '/guide/setting-up',
            '/cognitive-load' => '/blog/a-programmers-cognitive-load',
            '/key-binding' => '/blog/mastering-key-bindings',
            '/key-bindings' => '/blog/mastering-key-bindings',
            '/keybinds' => '/blog/mastering-key-bindings',
            '/keybind' => '/blog/mastering-key-bindings',
            '/keybinding' => '/blog/mastering-key-bindings',
            '/keybindings' => '/blog/mastering-key-bindings',
            '/curly' => '/blog/where-a-curly-bracket-belongs',
            '/blog/array-merge-vs' => '/blog/array-merge-vs+',
            '/newsletter-signup' => '/newsletter/subscribe',
            '/signup' => '/newsletter/subscribe',

            '/blog/php-81-new-in-inititalizers' => '/blog/php-81-new-in-initializers',

            '/youtube' => 'https://www.youtube.com/@phpannotated',
            '/yt' => 'https://www.youtube.com/@phpannotated',
        ];

        if ($redirect = $redirects[$request->path] ?? null) {
            return new Redirect($redirect);
        }

        return $next($request);
    }
}
<?php
/**
 * @var string|null $title The webpage's title
 */

use App\Blog\Meta;
use function Tempest\get;
use function Tempest\Http\csrf_token;
use Tempest\Core\AppConfig;
use function Tempest\Router\uri;

$title ??= null;
$meta ??= new Meta();
$meta->title ??= 'Stitcher.io';
$meta->description ??= 'A blog about modern PHP, the web, and programming in general. Follow my newsletter and YouTube channel as well.';
$meta->image ??= uri('/meta/meta_small.png');
$meta->canonical ??= null;
?>

<!doctype html>
<html lang="en" class="h-dvh flex flex-col md:p-4 bg-primary">
<head>
    <!-- General -->
    <title :if="$title">{{ $title }} | Stitcher.io</title>
    <title :else>Stitcher.io</title>
    <meta charset="UTF-8"/>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    <!-- Socials -->
    <meta property="og:title" :content="$meta->title"/>
    <meta property="twitter:title" :content="$meta->title"/>

    <meta property="og:description" :content="$meta->description"/>
    <meta property="twitter:description" :content="$meta->description"/>

    <meta property="og:image" :content="$meta->image"/>
    <meta property="twitter:image" :content="$meta->image"/>
    <meta name="image" :content="$meta->image"/>

    <link :if="$meta->canonical" rel="canonical" :href="$meta->canonical"/>

    <meta property="og:type" content="article"/>
    <meta name="twitter:card" content="summary_large_image"/>
    <meta name="twitter:creator" content="@brendt_gd"/>

    <!-- PWA -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
    <meta name="msapplication-TileColor" content="#fe2977">
    <meta name="theme-color" content="#fe2977">

    <!-- RSS -->
    <link rel="alternate" type="application/rss+xml" title="Stitcher RSS" href="/rss"/>

    <!-- Assets -->
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.6/dist/htmx.min.js" integrity="sha384-Akqfrbj/HpNVo8k11SXBb6TlBWmXXlYQrCSqEWmyKJe+hDm3Z/B2WVG4smwBkRVm" crossorigin="anonymous"></script>
    <x-vite-tags/>
    <x-slot name="head"/>
</head>
<body class="antialiased relative">
<div class="bg-gray-100 m-2 sm:m-0 p-0 p-2 md:p-4 rounded-md shadow-lg z-[10] mb-[30vh] md:mb-[20vh] relative">
    <x-slot/>
</div>

<div class="fixed bottom-0 p-2 z-[1] pb-[3vh]  w-full  text-white font-bold font-sm grid gap-4">
    <div class="flex justify-center">
            <span>
                Noticed a tpyo? You can <a href="https://github.com/brendt/stitcher.io">submit a PR</a> to fix it.
            </span>
    </div>
    <div class="flex flex-wrap gap-8 items-center justify-center">
        <a href="/">Home</a>
        <a href="/rss">RSS</a>
        <a href="/mail">Newsletter</a>
        <a href="https://tempestphp.com/discord">Discord</a>
        <span>&copy {{ \Tempest\DateTime\DateTime::now()->format('YYYY') }} stitcher.io</span>
    </div>
</div>

<x-slot name="scripts"/>
<script>
    document.body.addEventListener('htmx:configRequest', function (evt) {
        evt.detail.headers['x-xsrf-token'] = '{{ csrf_token() }}';
    });

    document.body.addEventListener('htmx:beforeOnLoad', function (evt) {
        if (evt.detail.xhr.status === 500) {
            document.querySelector('#htmx-error').innerHTML = evt.detail.xhr.statusText;
            document.querySelector('#htmx-error').classList.remove('hidden');
        }
    });
</script>

</body>
</html>

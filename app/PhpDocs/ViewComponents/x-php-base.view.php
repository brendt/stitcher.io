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
<html lang="en" class="h-dvh flex flex-col md:p-4 bg-ui">
<head>
    <!-- General -->
    <title :if="$title">{{ $title }} | Stitcher.io</title>
    <title :else>Stitcher.io</title>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>


    <!-- Assets -->
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.6/dist/htmx.min.js" integrity="sha384-Akqfrbj/HpNVo8k11SXBb6TlBWmXXlYQrCSqEWmyKJe+hDm3Z/B2WVG4smwBkRVm" crossorigin="anonymous"></script>
    <x-vite-tags/>
    <x-vite-tags entrypoint="app/PhpDocs/php-docs.css"/>

    <x-slot name="head"/>
</head>
<body class="antialiased relative">

<x-php-search/>

<div class="fixed w-full">
    <div class="bg-php/50 p-4 max-w-[1200px] mx-auto rounded-md shadow-md backdrop-blur flex items-center gap-4 justify-between">
        <div class="flex items-center gap-4">
            <a href="/php">
                <x-icon name="logos:php-alt" class="size-10 fill-white"/>
            </a>

            <div class="flex gap-2  items-center">
                <x-template :foreach="$breadcrumbs->loop() as $href => $name">
                    <span :if="$breadcrumbs->isLast" class="font-bold text-ui-accented p-2 rounded-md ">{{ $name }}</span>
                    <a :else :href="$href" class="bg-ui-accented p-2 rounded-md text-sm hover:bg-php-light hover:text-white cursor-pointer font-bold">
                        {{ $name }}
                    </a>
                </x-template>
            </div>
        </div>

        <div>
            <div
                    id="search-trigger"
                    class="
                        flex items-center gap-2
                        bg-ui-accented p-2 rounded-md text-sm hover:bg-php-light hover:text-white cursor-pointer font-bold
                    "
            >
                <span>cmd + k</span>
                <x-icon name="oui:search" class="size-3 fill-php"/>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-12 gap-4 mt-32">
    <div class="col-span-2">
        <x-slot name="left"/>
    </div>

    <div class="col-span-8">
        <x-slot/>
    </div>

    <div class="col-span-2">
        <x-slot name="right"/>
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

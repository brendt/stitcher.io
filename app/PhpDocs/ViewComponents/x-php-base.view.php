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
<html lang="en" class="h-dvh flex flex-col md:p-4">
<head>
    <!-- General -->
    <title :if="$title">{{ $title }} | Stitcher.io</title>
    <title :else>Stitcher.io</title>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>

    <!-- Dark mode -->
    <script>
        function isDark() {
            return localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
        }

        function applyTheme(theme = undefined) {
            if (theme) {
                localStorage.theme = theme
            }

            document.documentElement.classList.toggle('dark', isDark())
        }

        function toggleDarkMode() {
            applyTheme(isDark() ? 'light' : 'dark')
        }

        applyTheme();
    </script>

    <!-- Assets -->
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.6/dist/htmx.min.js" integrity="sha384-Akqfrbj/HpNVo8k11SXBb6TlBWmXXlYQrCSqEWmyKJe+hDm3Z/B2WVG4smwBkRVm" crossorigin="anonymous"></script>
    <x-vite-tags/>
    <x-vite-tags entrypoint="app/PhpDocs/php-docs.css"/>

    <x-slot name="head"/>
</head>
<body class="antialiased relative bg-(--ui-bg) text-(--ui-text)">

<x-php-search :matches="$matches ?? []" :keyword="$keyword ?? null"/>

<div class="fixed w-full">
    <div class="bg-(--ui-php)/60 p-4 max-w-[1200px] mx-auto md:rounded-md shadow-md backdrop-blur flex items-center gap-4 justify-between">
        <div class="flex items-center gap-4">
            <a href="/php">
                <x-icon name="logos:php-alt" class="size-10 fill-white"/>
            </a>

            <div class="gap-2  items-center hidden md:flex">
                <x-template :foreach="$breadcrumbs->loop() as $href => $name">
                    <span :if="$breadcrumbs->isLast" class="text-(--ui-white) bg-(--ui-php) p-2 rounded-md text-sm cursor-default font-bold">{{ $name }}</span>
                    <a :else :href="$href" class="text-(--ui-white) bg-(--ui-php) p-2 rounded-md text-sm hover:bg-(--ui-php-light) cursor-pointer font-bold">
                        {{ $name }}
                    </a>
                </x-template>
            </div>
        </div>

        <div class="flex gap-2 items-center">
            <div
                    id="search-trigger"
                    class="
                        flex items-center gap-2
                        text-(--ui-white) bg-(--ui-php) p-2 rounded-md text-sm hover:bg-(--ui-php-light) cursor-pointer font-bold
                    "
            >
                <span>cmd + k</span>
                <x-icon name="oui:search" class="size-3 fill-php"/>
            </div>

            <button id="toggle-theme" class="
                overflow-hidden transition hover:text-(--ui-primary)
                flex items-center gap-2
                text-(--ui-white) bg-(--ui-php) p-2 rounded-md text-sm hover:bg-(--ui-php-light) cursor-pointer font-bold
            ">
                <x-icon name="tabler:moon" class="size-5 dark:opacity-0 dark:translate-y-full duration-200"/>
            </button>
        </div>
    </div>
</div>

<div class="col-span-2 overflow-y-auto gap-4 max-h-full fixed pb-16 hidden md:grid">
    <x-slot name="left"/>
</div>

<div class="grid md:grid-cols-12 gap-4 mt-32 mb-32 px-4">
    <div class="hidden md:block md:col-span-2"></div>

    <div class="sm:px-0 w-full md:col-span-8">
        <x-slot/>
    </div>

    <div class="hidden md:block md:col-span-2">
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

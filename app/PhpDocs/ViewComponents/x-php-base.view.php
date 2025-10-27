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
<html lang="en" class="h-dvh">
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

<div class="bg-[#793862] text-center text-sm p-1 text-white w-full fixed top-0">
    This is a work-in-progess redesign of the official PHP docs.
</div>

<x-php-search :matches="$matches ?? []" :keyword="$keyword ?? null"/>
<x-php-header />

<main class="flex mx-auto px-4 xl:px-8 gap-4 grow mb-16 mt-4 md:mt-0">
    {{--
    <div data-save-scroll="docs-sidebar" class="hidden lg:block top-28 sticky xl:px-6 pt-4 xl:w-[20rem] max-h-[calc(100dvh-var(--ui-header-height))] overflow-auto shrink-0">
        <x-slot name="_left"/>
    </div>
    --}}

    <div class="w-full lg:max-w-[1200px] mx-auto">
        <x-slot/>
    </div>
</main>


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

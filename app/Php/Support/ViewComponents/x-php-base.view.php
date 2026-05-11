<?php
/**
 * @var string|null $title The webpage's title
 * @var bool|null $noPadding Remove main padding for full-width landing pages
 */

use App\Blog\Meta;
use function Tempest\Router\uri;

$title ??= null;
$noPadding ??= false;
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
    <title :if="$title">{{ $title }} | PHP docs (unofficial)</title>
    <title :else>PHP docs (unofficial)</title>
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

    <link rel="apple-touch-icon" sizes="180x180" href="/favicon-php/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-php/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-php/favicon-16x16.png">
    <link rel="manifest" href="/favicon-php/site.webmanifest">

    <!-- Assets -->
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.6/dist/htmx.min.js" integrity="sha384-Akqfrbj/HpNVo8k11SXBb6TlBWmXXlYQrCSqEWmyKJe+hDm3Z/B2WVG4smwBkRVm" crossorigin="anonymous"></script>
    <x-vite-tags entrypoint="app/main.entrypoint.css"/>
    <x-vite-tags entrypoint="app/Php/php.css"/>

    <x-slot name="head"/>
</head>
<body class="antialiased relative bg-(--ui-bg) text-(--ui-text)">

<!--<div class="bg-[#793862] text-center text-sm p-1 text-white w-full fixed top-0">-->
<!--    This is a third-party work-in-progess redesign of the official PHP docs.-->
<!--</div>-->

<x-php-search :matches="$matches ?? []" :keyword="$keyword ?? null"/>

<main class="flex gap-4 grow mb-16 mt-4 md:mt-0">
    <div class="w-full">
        <x-slot/>
    </div>
</main>

<x-slot name="scripts"/>

<script>
    document.body.addEventListener('htmx:beforeOnLoad', function (evt) {
        if (evt.detail.xhr.status === 500) {
            document.querySelector('#htmx-error').innerHTML = evt.detail.xhr.statusText;
            document.querySelector('#htmx-error').classList.remove('hidden');
        }
    });

    function openSearch() {
        const popup = document.getElementById('search-popup');
        if (!popup) return;
        popup.classList.remove('hidden');
        document.getElementById('search')?.focus();
    }

    function closeSearch() {
        document.getElementById('search-popup')?.classList.add('hidden');
    }

    document.addEventListener('keydown', function (e) {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            document.getElementById('search-popup')?.classList.contains('hidden') ? openSearch() : closeSearch();
        }
        if (e.key === 'Escape') closeSearch();
    });

    document.getElementById('search-popup')?.addEventListener('click', function (e) {
        if (e.target === this) closeSearch();
    });

    document.getElementById('search-trigger')?.addEventListener('click', openSearch);

    // Up/down/enter navigation in search results
    document.getElementById('search')?.addEventListener('keydown', function (e) {
        if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp' && e.key !== 'Enter') return;

        const results = [...document.querySelectorAll('.search-result')];
        if (!results.length) return;

        const selected = document.querySelector('.search-result[data-selected]');
        const index = selected ? results.indexOf(selected) : -1;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selected?.removeAttribute('data-selected');
            results[(index + 1) % results.length].setAttribute('data-selected', '');
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selected?.removeAttribute('data-selected');
            results[(index - 1 + results.length) % results.length].setAttribute('data-selected', '');
        } else if (e.key === 'Enter' && selected) {
            e.preventDefault();
            window.location.href = selected.href;
        }
    });

    // Clear selection when new results load
    document.getElementById('php-search-results')?.addEventListener('htmx:afterSettle', function () {
        document.querySelector('.search-result[data-selected]')?.removeAttribute('data-selected');
    });
</script>

</body>
</html>

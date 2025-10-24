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
<html lang="en" class="h-dvh flex flex-col md:p-4 bg-php">
<head>
    <!-- General -->
    <title :if="$title">{{ $title }} | Stitcher.io</title>
    <title :else>Stitcher.io</title>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>


    <!-- Assets -->
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.6/dist/htmx.min.js" integrity="sha384-Akqfrbj/HpNVo8k11SXBb6TlBWmXXlYQrCSqEWmyKJe+hDm3Z/B2WVG4smwBkRVm" crossorigin="anonymous"></script>
    <x-vite-tags/>
    <x-slot name="head"/>
</head>
<body class="antialiased relative">

<x-php-search />

<div class="bg-gray-100 m-2 sm:m-0 p-0 sm:p-2 md:p-4 rounded-md sm:shadow-lg relative">
    <x-slot/>
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

<?php
/**
 * @var string|null $title The webpage's title
 */

use App\Blog\Meta;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Core\Environment;

use function Tempest\Container\get;
use function Tempest\Router\uri;

$title ??= null;
$meta ??= new Meta();
if (($meta->title ?? null) === null) {
    $meta->title ??= $title ?? 'Getting started with PHP | stitcher.io';
} else {
    $meta->title .= ' | Getting started with PHP | stitcher.io';
}
$meta->description ??= "Learn PHP in " . date('Y');
$meta->canonical ??= null;
?>

<!doctype html>
<html lang="en">
<head>
    <!-- General -->
    <title>{{ $meta->title }}</title>
    <meta charset="UTF-8"/>

    <!-- Favicon -->
    <x-slot name="favicon">
        <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">
    </x-slot>

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

    <!-- Assets -->
    <x-vite-tags entrypoint="app/PHP/php.entrypoint.css"/>
    <x-slot name="head"/>
</head>
<body class="antialiased relative">

<x-slot/>

</body>
</html>


<?php
/**
 * @var string|null $title The webpage's title
 * @var \App\Blog\Meta|null $meta The webpage's metadata
 */

use App\Blog\Meta;
use Tempest\Http\Request;

use function Tempest\Container\get;
use function Tempest\Router\uri;

$title ??= null;
$meta ??= new Meta();

if (($meta->title ?? null) === null) {
    $meta->title = $title ?? 'Getting started with PHP';
} else {
    $meta->title .= ' | Getting started with PHP';
}
$meta->description ??= 'Learn modern PHP from the ground up: syntax, Composer, frameworks, QA tooling, deployment, HTTP, databases, and more.';
$meta->image ??= uri('/meta/meta_lg.png');
$meta->type ??= 'article';

$siteUrl = 'https://stitcher.io';
$absoluteUrl = static function (?string $url) use ($siteUrl): ?string {
    if ($url === null || $url === '') {
        return $url;
    }

    if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
        return $url;
    }

    return $siteUrl . '/' . ltrim($url, '/');
};

$meta->image = $absoluteUrl($meta->image);
$meta->canonical = $absoluteUrl($meta->canonical);
$meta->uri = $absoluteUrl($meta->uri);

$article = array_filter([
    '@type' => 'TechArticle',
    '@id' => ($meta->uri ?? $meta->canonical ?? "{$siteUrl}/php") . '#article',
    'headline' => $meta->title,
    'description' => $meta->description,
    'image' => $meta->image,
    'url' => $meta->uri,
    'mainEntityOfPage' => $meta->canonical,
    'isPartOf' => ['@id' => "{$siteUrl}/php#course"],
    'author' => [
        '@type' => 'Person',
        'name' => $meta->author ?? 'Brent Roose',
        'url' => $siteUrl,
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'stitcher.io',
        'url' => $siteUrl,
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $absoluteUrl(uri('/meta/meta_small.png')),
        ],
    ],
    'inLanguage' => 'en',
]);

if ($meta->jsonLd !== []) {
    $article = array_replace_recursive($article, $meta->jsonLd);
}

$graph = [
    [
        '@type' => 'WebSite',
        '@id' => "{$siteUrl}/#website",
        'url' => $siteUrl,
        'name' => 'stitcher.io',
        'inLanguage' => 'en',
    ],
    [
        '@type' => 'Course',
        '@id' => "{$siteUrl}/php#course",
        'name' => 'Getting started with PHP',
        'description' => 'A free course that teaches modern PHP, from syntax and Composer to frameworks, QA tooling, deployment, HTTP, databases, and more.',
        'url' => "{$siteUrl}/php",
        'provider' => [
            '@type' => 'Person',
            'name' => 'Brent Roose',
            'url' => $siteUrl,
        ],
        'inLanguage' => 'en',
    ],
    $article,
];

if ($meta->breadcrumbs !== []) {
    $items = [];
    $position = 1;

    foreach ($meta->breadcrumbs as $breadcrumb) {
        if (! is_array($breadcrumb)) {
            continue;
        }

        $name = $breadcrumb['name'] ?? null;
        $url = $breadcrumb['url'] ?? null;

        if (! is_string($name)) {
            continue;
        }

        $items[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $name,
            'item' => $absoluteUrl(is_string($url) ? $url : null),
        ];
        $position++;
    }

    $graph[] = [
        '@type' => 'BreadcrumbList',
        'itemListElement' => $items,
    ];
}

$structuredData = [
    '@context' => 'https://schema.org',
    '@graph' => $graph,
];
?>

<!doctype html>
<html lang="en" class="scroll-pt-20">
<head>
    <!-- General -->
    <title>{{ $meta->title }}</title>
    <meta charset="UTF-8"/>
    <meta name="description" :content="$meta->description"/>
    <meta :if="$meta->author" name="author" :content="$meta->author"/>
    <meta :if="$meta->keywords" name="keywords" :content="implode(', ', $meta->keywords)"/>
    <meta name="robots" content="index, follow, max-image-preview:large"/>

    <!-- Favicon -->
    <x-slot name="favicon">
        <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">
    </x-slot>

    <!-- Socials -->
    <meta property="og:title" :content="$meta->title"/>
    <meta name="twitter:title" :content="$meta->title"/>

    <meta property="og:description" :content="$meta->description"/>
    <meta name="twitter:description" :content="$meta->description"/>

    <meta property="og:url" :content="$meta->uri ?? $meta->canonical"/>
    <meta property="og:site_name" content="stitcher.io"/>
    <meta property="og:locale" content="en_US"/>
    <meta property="og:image" :content="$meta->image"/>
    <meta property="og:image:alt" :content="$meta->title"/>
    <meta name="twitter:image" :content="$meta->image"/>
    <meta name="twitter:image:alt" :content="$meta->title"/>
    <meta name="image" :content="$meta->image"/>

    <link :if="$meta->canonical" rel="canonical" :href="$meta->canonical"/>

    <meta property="og:type" :content="$meta->type"/>
    <meta :if="$meta->author" property="article:author" :content="$meta->author"/>
    <meta property="article:section" content="PHP"/>
    <meta name="twitter:card" content="summary_large_image"/>
    <meta name="twitter:site" content="@brendt_gd"/>
    <meta name="twitter:creator" content="@brendt_gd"/>
    <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>

    <!-- PWA -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
    <meta name="msapplication-TileColor" content="#fe2977">
    <meta name="theme-color" content="#fe2977">

    <!-- Assets -->
    <x-vite-tags entrypoint="app/PHP/php.entrypoint.css"/>
    <x-slot name="head"/>
    <!-- Prevent flash of wrong theme -->
    <script>if (localStorage.theme === 'dark' || (!localStorage.theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) { document.documentElement.classList.add('dark'); }</script>
</head>
<body class="antialiased relative">

<x-slot/>

</body>
</html>

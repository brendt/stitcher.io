<?php
/**
 * @var string|null $title The webpage's title
 */

use function Tempest\get;
use Tempest\Core\AppConfig;
$isProduction = get(AppConfig::class)->environment->isProduction();
?>

<!doctype html>
<html lang="en" class="h-dvh flex flex-col md:p-4 bg-primary">
<head>
    <title>{{ $title ?? 'stitcher.io' }}</title>

    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <x-slot name="head"/>

    <x-vite-tags />
</head>
<body class="antialiased relative">
    <div class="bg-transparent sm:bg-gray-100 m-2 sm:m-0 p-0 md:p-4 rounded-md shadow-lg z-[10] mb-[30vh] md:mb-[20vh] relative">
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
</body>
</html>

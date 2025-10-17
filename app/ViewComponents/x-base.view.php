<?php
/**
 * @var string|null $title The webpage's title
 */

//$color-black: #353535;
//$color-white: #ffffff;
//$color-orange: #fd5d1f;
//$color-orange-dark: #742100;
//$color-primary-pastel: #fcf4f5;
//$color-gray: #D3D3D3;
//$color-php: #8892bf;
//$color-breaking: #de5f5f;
//$color-primary: #fe2977;
//$color-secondary: #000;
//$color-body: #111;
//$color-primary-light: #FCF4F5;

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
    <div class="bg-gray-100 m-2 md:m-0 p-0 md:p-4 rounded-md shadow-lg z-[10] mb-[30vh] md:mb-[10vh] relative">
        <x-slot/>
    </div>

    <div class="fixed bottom-0 z-[1] pb-[3vh] flex flex-wrap gap-8 items-center justify-center w-full  text-white font-bold font-sm">
        <a href="/">Home</a>
        <a href="/rss">RSS</a>
        <a href="mailto:brendt@stitcher.io">Contact</a>
        <a href="https://tempestphp.com/discord">Discord</a>
        <span>&copy {{ \Tempest\DateTime\DateTime::now()->format('YYYY') }} stitcher.io</span>
    </div>

    <x-slot name="scripts"/>
</body>
</html>

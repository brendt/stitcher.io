<?php
/**
 * @var string|null $title The webpage's title
 */

use App\Blog\Meta;
use function Tempest\Router\uri;

$title ??= 'Things I wish I knew';
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

    <x-vite-tags entrypoint="app/main.entrypoint.css"/>
    <x-vite-tags entrypoint="app/Talks/things-talk.css"/>
</head>
<body class="antialiased relative">

<div class="max-w-[600px] mx-auto p-4 grid gap-2">
    <h1 class="my-8">Things I wish I knew</h1>

    <p>Thank you for attending my talk! If you have any thoughts, feedback, or followup questions, you can always reach out to me via <a href="mailto:brendt@stitcher.io">brendt@stitcher.io</a>.</p>

    <p>Here you'll find all relevant links and references:</p>

    <ul>
        <li>
            <a href="https://things-i-wish-i-knew.com/">Things I wish I knew when I started programming</a> — my newly published book, sharing many stories and lessons from the almost two decades I've been programming.
        </li>
        <li>
            <a href="https://www.youtube.com/watch?v=tbDDYKRFjhk">Does AI Actually Boost Developer Productivity?</a> — a very accessible talk from one of the researchers at the Standford team.
        </li>
        <li>
            <a href="https://www.wheresyoured.at/the-haters-gui/">The Hater's Guide to AI</a> — a one-hour long essay diving deep into the topic of AI's sustainability in the long run.
        </li>
        <li>
            <a href="https://curveshift.net/p/what-happens-if-ai-is-a-bubble">What happens if AI is a bubble?</a> — a balanced 5-minute read about whether AI is currently a bubble or not, and what would happen if it were.
        </li>
        <li>
            <a href="https://epoch.ai/blog/how-much-does-it-cost-to-train-frontier-ai-models">How much does it cost to train frontier AI models?</a> — a paper detailing the costs that come with training AI models.
        </li>
        <li>
            <a href="https://www.anthropic.com/research/AI-assistance-coding-skills">How AI assistance impacts the formation of coding skills</a> — a study by Anthropic.
        </li>
    </ul>
</div>

<div class="flex justify-center">
    <img src="/img/static/things-stick.svg" class="things-stick" alt="Background stick figure">
</div>

<div class="p-2 z-[1] pb-[3vh]  w-full  text-white font-bold font-sm grid gap-4">
    <div class="flex flex-wrap gap-8 items-center justify-center">
        <a href="/">Home</a>
        <a href="/rss">RSS</a>
        <a href="/mail">Newsletter</a>
        <a href="https://tempestphp.com/discord">Discord</a>
        <span>&copy {{ \Tempest\DateTime\DateTime::now()->format('YYYY') }} stitcher.io</span>
    </div>
</div>

</body>
</html>

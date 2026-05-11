<?php

?>

<x-php-base>
    <x-slot name="head">
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@graph": [
                {
                    "@type": "WebSite",
                    "name": "PHP Docs",
                    "url": "https://stitcher.io/php",
                    "description": "PHP is a general-purpose interpreted programming language for web and console development.",
                    "potentialAction": {
                        "@type": "SearchAction",
                        "target": {
                            "@type": "EntryPoint",
                            "urlTemplate": "https://stitcher.io/php/docs?q={search_term_string}"
                        },
                        "query-input": "required name=search_term_string"
                    }
                },
                {
                    "@type": "SoftwareApplication",
                    "name": "PHP",
                    "applicationCategory": "DeveloperApplication",
                    "operatingSystem": "Windows, macOS, Linux",
                    "url": "https://php.net",
                    "description": "PHP is a popular general-purpose scripting language that powers web development at every scale.",
                    "offers": {
                        "@type": "Offer",
                        "price": "0",
                        "priceCurrency": "USD"
                    }
                }
            ]
        }
        </script>
    </x-slot>

    {{-- -----------------------------------
         HERO SECTION
    ----------------------------------- --}}
    <section class="php-home-hero relative text-center px-6 h-[80svh] pt-[20svh]">
        {{-- Content --}}
        <div class="relative z-10 flex flex-col justify-between w-full h-full">
            {{-- Headline --}}
            <div class="w-full text-center grid gap-8">
                <h1 class="php-home-h1 grid gap-8">
                    <span class="text-base md:text-lg font-semibold tracking-[0.2em] uppercase text-(--ui-text-muted)">
                        PHP can do
                    </span>
                    <span>anything.</span>
                </h1>
                <p class="text-lg md:text-xl text-(--ui-text-muted) max-w-2xl leading-relaxed mx-auto">
                    PHP is a <span class="font-bold">general-purpose interpreted programming language</span> for web and console development. It powers anything from personal blogs to platforms serving billions.
                </p>
            </div>

            {{-- CTAs --}}
            <div class="flex flex-wrap items-center justify-center gap-4">
                <a href="#get-started" class="php-home-cta php-home-cta--primary">
                    Get started
                </a>
                <a href="/php/docs" class="php-home-cta php-home-cta--ghost flex gap-2">
                    Browse the docs
                </a>
            </div>
        </div>
    </section>

    {{-- -----------------------------------
         MODERN PHP CODE SHOWCASE
    ----------------------------------- --}}
    <section class="py-20 px-6">
        <div class="max-w-3xl mx-auto">
            {{-- Code window --}}
            <div class="php-home-code-window shadow-2xl">
                {{-- Title bar --}}
                <div class="flex items-center gap-2 px-5 py-3 bg-(--ui-php) rounded-t-xl">
                    <div class="w-3 h-3 rounded-full bg-white/25"></div>
                    <div class="w-3 h-3 rounded-full bg-white/25"></div>
                    <div class="w-3 h-3 rounded-full bg-white/25"></div>
                    <span class="ml-3 text-white/70 text-sm font-mono tracking-wide">index.php</span>
                </div>
                {{-- Code body --}}
                <div class="bg-[#1e1e2e] rounded-b-xl p-6 md:p-8 overflow-x-auto">
                    {!! $snippet !!}
                </div>
            </div>

        </div>
    </section>

    {{-- -----------------------------------
         BUILT WITH PHP
    ----------------------------------- --}}
    <section class="php-home-projects py-20 px-6 mb-16">
        <div class="max-w-5xl mx-auto">

            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-(--ui-text) mb-3">
                    Built with PHP
                </h2>
                <p class="text-(--ui-text-muted) text-lg max-w-xl mx-auto">
                    From indie projects to platforms used by billions — PHP powers software you rely on every day.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                <div class="php-home-card">
                    <div class="php-home-card-icon" style="--card-hue: 210">W</div>
                    <h3>WordPress</h3>
                    <p>The world's most popular CMS. Powers 43% of all websites on the internet.</p>
                    <span class="php-home-card-stat">43% of the web</span>
                </div>

                <div class="php-home-card">
                    <div class="php-home-card-icon" style="--card-hue: 0">L</div>
                    <h3>Laravel</h3>
                    <p>The PHP framework for web artisans. Elegant syntax for building modern applications.</p>
                    <span class="php-home-card-stat">30M+ installs</span>
                </div>

                <div class="php-home-card">
                    <div class="php-home-card-icon" style="--card-hue: 160">W</div>
                    <h3>Wikipedia</h3>
                    <p>The free encyclopedia, serving billions of page views per month on PHP.</p>
                    <span class="php-home-card-stat">17B+ page views/month</span>
                </div>

            </div>
        </div>
    </section>

    {{-- -----------------------------------
         GETTING STARTED SECTION
    ----------------------------------- --}}
    <section class="py-20 px-6" id="get-started">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-(--ui-text) mb-3">
                    Getting started with PHP
                </h2>
            </div>

            <x-php-install />

            {{-- CTA --}}
            <div class="mt-10 text-center">
                <a href="/php/learn" class="php-home-cta php-home-cta--primary">
                    Learn PHP
                </a>
            </div>
        </div>
    </section>
</x-php-base>

<?php

?>

<x-php-base>

    {{-- -----------------------------------
         HERO SECTION
    ----------------------------------- --}}
    <section class="php-home-hero relative text-center px-6 h-[80svh] pt-[20svh]">
        {{-- Content --}}
        <div class="relative z-10 flex flex-col justify-between w-full h-full">
            {{-- Headline --}}
            <div class="w-full text-center grid gap-8">
                <p class="text-base md:text-lg font-semibold tracking-[0.2em] uppercase text-(--ui-text-muted)">
                    PHP can do
                </p>
                <h1 class="php-home-h1">
                    anything.
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

            {{-- Step 1 --}}
            <div class="php-home-step">
                <div class="php-home-step-label">Step 1 — Install</div>

                {{-- OS Tabs --}}
                <div class="php-home-os-tab-list" role="tablist">
                    <button class="php-home-os-tab active" data-os="mac" onclick="switchOs('mac')">macOS</button>
                    <button class="php-home-os-tab" data-os="windows" onclick="switchOs('windows')">Windows</button>
                    <button class="php-home-os-tab" data-os="linux" onclick="switchOs('linux')">Linux</button>
                </div>

                <div data-os-panel="mac">
                    <div class="php-home-install-block">
                        <code>brew install php</code>
                        <button class="php-home-copy-btn" onclick="copyCmd(this, 'brew install php')">copy</button>
                    </div>
                    <p class="text-sm text-(--ui-text-muted) mt-3">
                        Don't have Homebrew yet? Install it at <a class="underline" href="https://brew.sh" target="_blank">brew.sh</a> — it's a one-liner.
                    </p>
                </div>

                <div data-os-panel="windows" class="hidden">
                    <div class="php-home-install-block">
                        <code>winget install PHP.PHP</code>
                        <button class="php-home-copy-btn" onclick="copyCmd(this, 'winget install PHP.PHP')">copy</button>
                    </div>
                    <p class="text-sm text-(--ui-text-muted) mt-3">
                        winget comes built into Windows 10 and 11. Prefer a GUI? Try <a class="underline" href="https://laragon.org" target="_blank">Laragon</a> — a zero-config local environment.
                    </p>
                </div>

                <div data-os-panel="linux" class="hidden">
                    <div class="php-home-install-block">
                        <code>sudo apt install php</code>
                        <button class="php-home-copy-btn" onclick="copyCmd(this, 'sudo apt install php')">copy</button>
                    </div>
                    <p class="text-sm text-(--ui-text-muted) mt-3">
                        For other PHP versions, add the <a class="underline" href="https://launchpad.net/~ondrej/+archive/ubuntu/php" target="_blank">ondrej/php</a> repository first.
                    </p>
                </div>
            </div>

            {{-- Step 2 --}}
            <div class="php-home-step">
                <div class="php-home-step-label">Step 2 — Verify</div>
                <div class="php-home-install-block">
                    <code>php --version</code>
                    <button class="php-home-copy-btn" onclick="copyCmd(this, 'php --version')">copy</button>
                </div>
                <p class="text-sm text-(--ui-text-muted) mt-3">
                    You should see something like <code>PHP 8.4.x (cli)</code>. If you do — you're all set.
                </p>
            </div>

            {{-- CTA --}}
            <div class="mt-10 text-center">
                <a href="/php/learn" class="php-home-cta php-home-cta--primary">
                    Learn PHP
                </a>
            </div>

        </div>
    </section>

    <x-slot name="scripts">
        <script>
            function switchOs(os) {
                document.querySelectorAll('[data-os-panel]').forEach(p => p.classList.add('hidden'));
                document.querySelector('[data-os-panel="' + os + '"]').classList.remove('hidden');
                document.querySelectorAll('.php-home-os-tab').forEach(t => t.classList.remove('active'));
                document.querySelector('[data-os="' + os + '"]').classList.add('active');
            }

            function copyCmd(btn, text) {
                navigator.clipboard.writeText(text).then(() => {
                    btn.textContent = 'copied!';
                    setTimeout(() => btn.textContent = 'copy', 1800);
                });
            }
        </script>
    </x-slot>

</x-php-base>

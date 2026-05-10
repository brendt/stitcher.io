<?php

use function Tempest\Router\uri;
use App\PhpDocs\PhpDocsController;

?>

<x-php-base :noPadding="true">

    {{-- ========================================================
         HERO SECTION
    ======================================================== --}}
    <section class="php-home-hero relative overflow-hidden flex flex-col items-center justify-center text-center px-6 pt-16 pb-24 min-h-[90vh]">
        {{-- Content --}}
        <div class="relative z-10 flex flex-col items-center gap-8 w-full">
            {{-- Headline --}}
            <div class="w-full text-center">
                <p class="text-base md:text-lg font-semibold tracking-[0.2em] uppercase text-(--ui-text-muted) mb-4">
                    What can PHP do?
                </p>
                <h1 class="php-home-h1">
                    Anything.
                </h1>
            </div>

            {{-- Subtitle --}}
            <p class="text-lg md:text-xl text-(--ui-text-muted) max-w-2xl leading-relaxed mx-auto">
                PHP powers <strong class="font-semibold text-(--ui-text)">78% of the web</strong> — from
                personal blogs to platforms serving billions. Fast, expressive, and
                more exciting than you think.
            </p>

            {{-- CTAs --}}
            <div class="flex flex-wrap items-center justify-center gap-4">
                <a href="/php/language.intro" class="php-home-cta php-home-cta--primary">
                    Get started with PHP
                    <svg class="w-4 h-4 ml-1.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="/php/about" class="php-home-cta php-home-cta--secondary">
                    Learn more
                </a>
                <a href="/php/funcref" class="php-home-cta php-home-cta--ghost">
                    Browse the docs
                </a>
            </div>

            {{-- Version badge --}}
            <div class="flex flex-wrap items-center justify-center gap-3 text-sm text-(--ui-text-muted)">
                <span class="php-home-badge">PHP 8.4 is here</span>
                <span class="hidden sm:inline opacity-40">·</span>
                <span class="hidden sm:inline">Enums · Fibers · JIT · Readonly classes · Match expressions</span>
            </div>

        </div>
    </section>

    {{-- ========================================================
         MODERN PHP CODE SHOWCASE
    ======================================================== --}}
    <section class="py-20 px-6">
        <div class="max-w-3xl mx-auto">

            <div class="text-center mb-10">
                <h2 class="text-3xl md:text-4xl font-bold text-(--ui-text) mb-3">
                    Modern PHP, right now
                </h2>
                <p class="text-(--ui-text-muted) text-lg">
                    PHP 8.x brings powerful new syntax that makes your code cleaner, safer, and faster.
                </p>
            </div>

            {{-- Code window --}}
            <div class="php-home-code-window shadow-2xl">
                {{-- Title bar --}}
                <div class="flex items-center gap-2 px-5 py-3 bg-(--ui-php) rounded-t-xl">
                    <div class="w-3 h-3 rounded-full bg-white/25"></div>
                    <div class="w-3 h-3 rounded-full bg-white/25"></div>
                    <div class="w-3 h-3 rounded-full bg-white/25"></div>
                    <span class="ml-3 text-white/70 text-sm font-mono tracking-wide">modern.php</span>
                </div>
                {{-- Code body --}}
                <div class="bg-[#1e1e2e] rounded-b-xl p-6 md:p-8 overflow-x-auto">
<pre class="font-mono text-sm md:text-[0.9rem] leading-[1.8] text-[#cdd6f4]"><span class="php-tok-kw">enum</span> <span class="php-tok-cls">Status</span>: <span class="php-tok-type">string</span>
{
    <span class="php-tok-kw">case</span> Active   = <span class="php-tok-str">'active'</span>;
    <span class="php-tok-kw">case</span> Inactive = <span class="php-tok-str">'inactive'</span>;
}

<span class="php-tok-kw">readonly</span> <span class="php-tok-kw">class</span> <span class="php-tok-cls">User</span>
{
    <span class="php-tok-kw">public function</span> <span class="php-tok-fn">__construct</span>(
        <span class="php-tok-kw">public</span> <span class="php-tok-type">string</span>  <span class="php-tok-var">$name</span>,
        <span class="php-tok-kw">public</span> <span class="php-tok-cls">Status</span>  <span class="php-tok-var">$status</span>    = Status::Active,
        <span class="php-tok-kw">public</span> <span class="php-tok-type">DateTime</span> <span class="php-tok-var">$createdAt</span> = <span class="php-tok-kw">new</span> <span class="php-tok-cls">DateTime</span>(),
    ) {}
}

<span class="php-tok-var">$user</span>    = <span class="php-tok-kw">new</span> User(name: <span class="php-tok-str">'Alice'</span>);
<span class="php-tok-var">$message</span> = <span class="php-tok-kw">match</span> (<span class="php-tok-var">$user</span>-><span class="php-tok-prop">status</span>) {
    Status::Active   => <span class="php-tok-str">"Welcome back, {<span class="php-tok-var">$user</span>-><span class="php-tok-prop">name</span>}!"</span>,
    Status::Inactive => <span class="php-tok-str">"Please reactivate your account."</span>,
};</pre>
                </div>
            </div>

        </div>
    </section>

    {{-- ========================================================
         BUILT WITH PHP
    ======================================================== --}}
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

                <div class="php-home-card">
                    <div class="php-home-card-icon" style="--card-hue: 250">S</div>
                    <h3>Symfony</h3>
                    <p>High-performance framework trusted by enterprises for complex, large-scale apps.</p>
                    <span class="php-home-card-stat">1B+ downloads</span>
                </div>

                <div class="php-home-card">
                    <div class="php-home-card-icon" style="--card-hue: 290">S</div>
                    <h3>Slack</h3>
                    <p>The popular team communication platform, originally built on PHP and Hack.</p>
                    <span class="php-home-card-stat">20M+ daily users</span>
                </div>

                <div class="php-home-card">
                    <div class="php-home-card-icon" style="--card-hue: 30">E</div>
                    <h3>Etsy</h3>
                    <p>The global marketplace for handmade and vintage items, built on PHP infrastructure.</p>
                    <span class="php-home-card-stat">90M+ buyers</span>
                </div>

            </div>
        </div>
    </section>

</x-php-base>

<?php
/** @var \App\PHP\GettingStarted\GettingStartedPage $page */
/** @var \Tempest\Support\Arr\ImmutableArray $pages */
?>
<x-php-base :meta="$page->meta">
    <x-slot name="head">
        <link :if="$page->previous" rel="prev" :href="$page->previous->uri"/>
        <link :if="$page->next" rel="next" :href="$page->next->uri"/>
        <x-vite-tags entrypoint="app/PHP/php-toc.entrypoint.ts"/>
    </x-slot>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-20 shadow-sm">
            <div class="max-w-screen-xl 2xl:max-w-screen-2xl mx-auto flex justify-between items-center flex-wrap">
                <div class="px-6 h-14 flex items-center gap-3 min-w-0">
                    <a href="/php" class="font-bold  text-lg text-primary tracking-tight">Getting Started with PHP</a>
                    <span class="text-gray-300 dark:text-gray-600 select-none hidden md:inline">/</span>
                    <span class="text-gray-500 dark:text-gray-400 text-sm truncate hidden md:inline">{{ $page->categoryName }}</span>
                    <span class="text-gray-300 dark:text-gray-600 select-none hidden md:inline">/</span>
                    <span class="text-gray-500 dark:text-gray-400 text-sm truncate hidden md:inline">{{ $page->title }}</span>
                </div>

                <div class="flex items-center gap-2 pr-4">
                    <div class="items-center gap-2 hidden sm:flex">
                        <a
                                href="https://github.com/brendt/stitcher.io/tree/main/app/PHP/GettingStarted/Content"
                                class="flex gap-2 items-center text-white bg-primary px-4 py-2 text-sm font-bold shadow-sm rounded-full group">
                            <x-icon name="mdi:github" />Contribute<span class="group-hover:inline hidden"> — much appreciated!</span>
                        </a>
                        <button id="dark-mode-toggle" aria-label="Toggle dark mode" class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <!-- Moon (shown in light mode) -->
                            <svg class="dark:hidden w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z"/>
                            </svg>
                            <!-- Sun (shown in dark mode) -->
                            <svg class="hidden dark:block w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Mobile chapter menu toggle -->
                    <button
                            id="chapter-menu-toggle"
                            type="button"
                            aria-label="Toggle chapters menu"
                            aria-controls="chapter-menu"
                            aria-expanded="false"
                            class="md:hidden p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    >
                        <x-icon name="mdi:menu" class="w-5 h-5 block" data-menu-open />
                        <x-icon name="mdi:close" class="w-5 h-5 hidden" data-menu-close />
                    </button>
                </div>
            </div>

            <!-- Mobile chapter menu (collapsible) -->
            <div id="chapter-menu" class="hidden md:hidden border-t border-gray-200 dark:border-gray-700 max-w-screen-xl 2xl:max-w-screen-2xl mx-auto px-6 py-4">
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-2">Chapters</p>
                <div class="flex flex-wrap gap-2">
                    <x-template :foreach="$pages as $category => $perCategory">
                        <a
                                :foreach="$perCategory as $other"
                                :href="$other->uri"
                                class="px-3 py-1 rounded-full text-sm font-medium"
                                :class="$other->slug === $page->slug
                            ? 'bg-primary text-white'
                            : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                        >{{ $other->title }}</a>
                    </x-template>
                </div>
            </div>
        </header>

        <div class="max-w-screen-xl 2xl:max-w-screen-2xl mx-auto px-2 md:px-6 py-4 md:py-10 flex gap-12 items-start">
            <!-- Sidebar (desktop) -->
            <aside class="hidden md:block w-52 shrink-0 sticky top-20">
                <nav class="flex flex-col gap-4">
                    <div :foreach="$pages as $category => $perCategory" class="flex flex-col gap-0.5">
                        <span class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-2">{{ $category }}</span>
                        <a
                                :foreach="$perCategory as $other"
                                :href="$other->uri"
                                class="px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                                :class="$other->slug === $page->slug
                            ? 'bg-primary text-white shadow-sm'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'"
                        ><span class="text-xs">{{ $other->index }}.</span>&nbsp;{{ $other->title }}</a>
                    </div>

                </nav>
            </aside>

            <!-- Main content -->
            <main class="flex-1 min-w-0">
                <a
                    href="https://github.com/brendt/stitcher.io/tree/main/app/PHP/GettingStarted/Content"
                    class="
                        block
                        bg-primary/30   text-primary font-bold
                        dark:bg-gray-800 dark:text-primary
                        dark:border-gray-700 dark:border
                        mb-4 p-4 md:px-10 rounded-lg shadow-sm
                        group
                    "
                >
                    Work in progress! If you're an experienced PHP developer, it would be awesome if you could help by providing feedback or sending pull requests <span class="underline group-hover:no-underline">on GitHub</span>.
                </a>

                <article class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 px-4 md:px-8 py-6 md:px-14 md:py-14">
                    <h1 class="text-4xl font-bold text-primary leading-tight mb-4 md:mb-8">{{ $page->title }}</h1>
                    {!! $page->content !!}
                </article>

                <div class="mt-6 flex-wrap flex items-center gap-4 justify-center sm:justify-between">
                    <a
                        :if="$page->previous"
                        :href="$page->previous->uri"
                        class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
                    >
                        ← Previous: {{ $page->previous->title }}
                    </a>
                    <span :else></span>

                    <a
                        :if="$page->next"
                        :href="$page->next->uri"
                        class="flex items-center gap-2 bg-primary text-white font-bold rounded-full px-6 py-3 shadow-sm hover:shadow-md transition-shadow text-sm"
                    >
                        Next: {{ $page->next->title }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

            </main>

            <!-- On this page (desktop) -->
            <aside :if="$page->sections" class="hidden lg:block w-48 shrink-0 sticky top-20">
                <nav class="flex flex-col gap-1">
                    <span class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-2">On this page</span>
                    <a
                        :foreach="$page->sections as $id => $title"
                        :href="$id"
                        data-toc-link
                        class="px-3 py-1.5 rounded-lg text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    >{!! $title !!}</a>
                </nav>
            </aside>
        </div>



        <footer class="flex justify-center py-4 text-gray-500">
            <p>&copy; {{ date('Y') }}  <a href="https://stitcher.io" class="underline hover:no-underline">stitcher.io</a></p>
        </footer>
    </div>
</x-php-base>

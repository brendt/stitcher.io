<?php
/** @var \App\PHP\GettingStarted\GettingStartedPage $page */
/** @var \Tempest\Support\Arr\ImmutableArray $pages */
?>
<x-php-base :meta="$page->meta">
    <x-slot name="head">
        <x-vite-tags entrypoint="app/PHP/php-toc.entrypoint.ts"/>
    </x-slot>

    <div class="min-h-screen bg-gray-50">

        <!-- Header -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-20 shadow-sm">
            <div class="max-w-screen-xl 2xl:max-w-screen-2xl mx-auto flex justify-between">
                <div class="px-6 h-14 flex items-center gap-3">
                    <a href="/php" class="font-bold text-lg text-primary tracking-tight">Getting Started with PHP</a>
                    <span class="text-gray-300 select-none hidden md:inline">/</span>
                    <span class="text-gray-500 text-sm truncate hidden md:inline">{{ $page->categoryName }}</span>
                    <span class="text-gray-300 select-none hidden md:inline">/</span>
                    <span class="text-gray-500 text-sm truncate hidden md:inline">{{ $page->title }}</span>
                </div>

                <div class="flex items-center">
                    <a
                            href="https://github.com/brendt/stitcher.io"
                            class="flex gap-2 items-center text-white bg-primary px-4 py-2 text-sm font-bold shadow-sm rounded-full group">
                        <x-icon name="mdi:github" />Contribute<span class="group-hover:inline hidden"> — much appreciated!</span>
                    </a>
                </div>
            </div>
        </header>

        <div class="max-w-screen-xl 2xl:max-w-screen-2xl mx-auto px-6 py-10 flex gap-12 items-start">
            <!-- Sidebar (desktop) -->
            <aside class="hidden md:block w-52 shrink-0 sticky top-20">
                <nav class="flex flex-col gap-4">
                    <div :foreach="$pages as $category => $perCategory" class="flex flex-col gap-0.5">
                        <span class="text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">{{ $category }}</span>
                        <a
                                :foreach="$perCategory as $other"
                                :href="$other->uri"
                                class="px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                                :class="$other->slug === $page->slug
                            ? 'bg-primary text-white shadow-sm'
                            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                        ><span class="text-xs">{{ $other->index }}.</span>&nbsp;{{ $other->title }}</a>
                    </div>

                </nav>
            </aside>

            <!-- Main content -->
            <main class="flex-1 min-w-0">
                <!-- Mobile chapter nav -->
                <div class="md:hidden mb-6">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">Chapters</p>
                    <div class="flex flex-wrap gap-2">
                        <x-template :foreach="$pages as $category => $perCategory">
                            <a
                                    :foreach="$perCategory as $other"
                                    :href="$other->uri"
                                    class="px-3 py-1 rounded-full text-sm font-medium"
                                    :class="$other->slug === $page->slug
                                ? 'bg-primary text-white'
                                : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'"
                            >{{ $other->title }}</a>
                        </x-template>
                    </div>
                </div>

                <article class="bg-white rounded-xl shadow-sm border border-gray-100 px-8 py-10 md:px-14 md:py-14">
                    <h1 class="text-4xl font-bold text-primary leading-tight mb-8">{{ $page->title }}</h1>
                    {!! $page->content !!}
                </article>

                <div class="mt-6 flex items-center justify-between">
                    <a href="/php" class="text-sm text-gray-500 hover:text-gray-800 transition-colors">
                        ← All chapters
                    </a>
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
                    <span class="text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">On this page</span>
                    <a
                        :foreach="$page->sections as $id => $title"
                        :href="$id"
                        data-toc-link
                        class="px-3 py-1.5 rounded-lg text-sm text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                    >{{ $title }}</a>
                </nav>
            </aside>
        </div>
    </div>
</x-php-base>

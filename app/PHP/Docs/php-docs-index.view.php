<?php

?>

<x-php-base>
    <div class="flex flex-col items-center justify-center min-h-[85vh] px-6 py-20">

        {{-- Logo --}}
        <x-icon name="logos:php-alt" class="w-16 h-16 mb-8"/>

        {{-- Heading --}}
        <h1 class="text-3xl md:text-4xl font-bold text-(--ui-text) mb-3 text-center">
            PHP Documentation
        </h1>
        <p class="text-(--ui-text-muted) text-lg mb-10 text-center">
            Search for functions, classes, and language features.
        </p>

        {{-- Search trigger --}}
        <button onclick="openSearch()" class="php-docs-index-search w-full max-w-lg">
            <x-icon name="oui:search" class="size-4 shrink-0 opacity-50"/>
            <span>Search the docs…</span>
            <kbd>⌘K</kbd>
        </button>

        {{-- Quick navigation --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-12 w-full max-w-lg">
            <a href="/php/langref" class="php-docs-index-card">
                <span>Language reference</span>
                <x-icon name="heroicons:arrow-right-16-solid" class="size-4 shrink-0 opacity-40"/>
            </a>
            <a href="/php/funcref" class="php-docs-index-card">
                <span>Function reference</span>
                <x-icon name="heroicons:arrow-right-16-solid" class="size-4 shrink-0 opacity-40"/>
            </a>
        </div>

    </div>

    <x-slot name="scripts">
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                openSearch();
            });
        </script>
    </x-slot>

</x-php-base>

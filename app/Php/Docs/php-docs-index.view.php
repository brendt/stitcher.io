<?php

?>

<x-php-base>
    <x-php-header />

    <div class="flex flex-col items-center justify-center gap-8 min-h-[70vh] px-6 py-20">

        <h1 class="php-home-h1">The docs.</h1>

        {{-- Search trigger --}}
        <button onclick="openSearch()" class="php-docs-index-search w-full max-w-lg">
            <x-icon name="oui:search" class="size-4 shrink-0 opacity-50"/>
            <span>Search…</span>
            <kbd>⌘K</kbd>
        </button>

        {{-- Quick navigation --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full max-w-lg">
            <a href="/php/docs/language" class="php-docs-index-card">
                <span>Language reference</span>
                <x-icon name="heroicons:arrow-right-16-solid" class="size-4 shrink-0 opacity-40"/>
            </a>
            <a href="/php/docs/reference" class="php-docs-index-card">
                <span>Function reference</span>
                <x-icon name="heroicons:arrow-right-16-solid" class="size-4 shrink-0 opacity-40"/>
            </a>
        </div>

    </div>

</x-php-base>

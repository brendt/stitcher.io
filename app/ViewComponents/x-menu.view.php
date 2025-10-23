<div class="mt-4 mb-2 sm:mt-8 sm:mb-4 grid gap-2">
    <h1 class="text-white text-4xl font-bold sm:text-primary text-center sm:text-left"><x-slot>stitcher.io</x-slot></h1>

    <div class="flex flex-wrap gap-2 justify-center sm:justify-start">
        <x-button class="sm:border-none border-2 border-white" :small href="/">Blog</x-button>
        <x-button class="sm:border-none border-2 border-white" :small href="/mail/archive">Newsletter</x-button>
        <x-button class="sm:border-none border-2 border-white" :small href="/rss">Feed</x-button>
        <x-button class="sm:border-none border-2 border-white" :small href="/books">Books</x-button>
        <x-button class="sm:border-none border-2 border-white" :small href="https://tempestphp.com" class="hidden sm:block">Tempest</x-button>
    </div>
</div>
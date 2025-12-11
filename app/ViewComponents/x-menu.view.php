<div class="mt-4 mb-2 sm:mt-8 sm:mb-4 grid gap-2">
    <h1 class=" text-4xl font-bold text-primary text-center sm:text-left"><x-slot><a href="/">stitcher.io</a></x-slot></h1>

    <div class="flex flex-wrap gap-2 justify-center sm:justify-start">
        <x-button :small href="/">Blog</x-button>
        <x-button :small href="/mail/archive">Newsletter</x-button>
        <x-button :small href="/feed">Feed</x-button>
        <x-button :small href="/books">Books</x-button>
        <x-button :small href="https://tempestphp.com" class="hidden sm:block">Tempest</x-button>
    </div>
</div>
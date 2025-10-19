<x-base>
    <x-container>
        <div class="my-4 sm:my-8 grid gap-2">
            <h1 class="text-4xl font-bold text-primary text-center sm:text-left">stitcher.io</h1>
            <div class="flex flex-wrap gap-2 justify-center sm:justify-start">
                <x-button :small href="/rss">Feed</x-button>
                <x-button :small href="/mail">Newsletter</x-button>
                <x-button :small href="/">Books</x-button>
                <x-button :small href="https://tempestphp.com">Tempest</x-button>
            </div>
        </div>

        <nav class="grid gap-2">
            <a :href="$post->uri" :foreach="$posts as $post" class="p-3 px-4 bg-white shadow-md hover:shadow-lg rounded-sm grid hover:text-primary hover:underline">
                <span class="font-bold">
                    {{ $post->title }}
                </span>
                <span class="text-sm  hover:text-inherit">
                    {{ $post->date->format('YYYY-MM-dd') }}
                </span>
            </a>
        </nav>
    </x-container>
</x-base>
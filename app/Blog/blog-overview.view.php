<x-base title="Blog">
    <x-container>
        <div class="my-4 sm:my-8 grid gap-2">
            <h1 class="text-4xl font-bold text-primary text-center sm:text-left">stitcher.io - blog</h1>
            <x-menu />
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
<x-base>
    <x-container>
        <nav class="grid gap-4">
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
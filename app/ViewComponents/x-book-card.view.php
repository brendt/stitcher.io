<x-card :title="$title" :img="$img" :href="$href">
    <h2>{{ $title }}</h2>
    <div class="flex-col items-center sm:items-start sm:flex-row flex gap-4">
        <a :href="$href" class="max-w-[40%]">
            <img :src="$img" :alt="$title . ' cover image'" class="rounded-xs max-w-full shadow-md">
        </a>
        <div class="grid gap-2 sm:max-w-[60%]">
            <x-slot />
        </div>
    </div>
    <p><a :href="$href">Read more</a></p>
</x-card>

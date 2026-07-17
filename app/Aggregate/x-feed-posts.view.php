<?php

use App\Aggregate\Posts\PostsController;

use function Tempest\Router\uri;

?>

<div id="feed-posts" class="grid gap-2">
    <div
            :foreach="$posts as $index => $post"
            class="rounded-xs bg-white shadow-sm hover:shadow-lg flex items-center justify-between"
    >
        <div class="pl-4">
            <span class="text-md sm:text-xs p-1 px-2 rounded-sm {{ $color($post) }}">{{ $post->visits }}</span>
        </div>

        <a :href="uri([PostsController::class, 'visit'], post: $post->id)" class="hover:underline grow p-4">
            <span class="font-bold wrap-anywhere">{{ $post->title }}</span>
            <x-template :if="$post->source">
                <br class="inline sm:hidden">
                <span class="hidden sm:inline">&nbsp;</span>
                <span class="text-sm block overflow-hidden">{{ $post->sourceName }}</span>
            </x-template>
        </a>

        <div class="flex p-4 cursor-pointer group copy-uri" :data-uri="uri([PostsController::class, 'visit'], post: $post->id)">
            <x-icon
                    name="lucide:link"
                    class="icon-copy size-7 sm:size-6 p-1 bg-gray-100 rounded-sm group-hover:bg-gray-200"
            />
            <x-icon
                    name="lucide:check"
                    class="icon-copied size-7 sm:size-6 p-1 bg-emerald-600 text-emerald-50 rounded-sm"
            />
        </div>
    </div>
</div>

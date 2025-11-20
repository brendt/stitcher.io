<?php
use function Tempest\Router\uri;
use App\Aggregate\Posts\PostsController;
?>

<div id="pending-posts" class="grid gap-2">
    <div
            :foreach="$pendingPosts as $pendingPost"
            class="p-2 pl-4 rounded-lg shadow-sm bg-gray-200 flex gap-2 flex-col sm:flex-row items-center justify-between"
    >
        <span class="text-gray-500">
            <span class="font-bold wrap-anywhere">{{ $pendingPost->title }}</span>&nbsp;<span class="text-sm break-all">–&nbsp;{{ $pendingPost->source->name }}</span>
        </span>

        <div class="flex gap-8 sm:gap-2">
            <a class="bg-gray-100 p-2 rounded-md htmx-button flex items-center" :href="$pendingPost->uri">
                <x-icon name="lucide:external-link" class="size-6 sm:size-5 text-gray-400"/>
            </a>

            <x-action-button :if="$shouldQueue" :action="uri([PostsController::class, 'publish'], post: $pendingPost->id)" target="#pending-posts">
                <x-icon name="lucide:check" class="size-6 sm:size-5 text-gray-400"/>
            </x-action-button>

            <x-action-button :action="uri([PostsController::class, 'deny'], post: $pendingPost->id)" target="#pending-posts">
                <x-icon name="lucide:trash-2" class="size-6 sm:size-5 text-gray-400"/>
            </x-action-button>

            <x-action-button :if="!$shouldQueue" :action="uri([PostsController::class, 'publish'], post: $pendingPost->id)" target="#pending-posts">
                <x-icon name="lucide:check" class="size-6 sm:size-5 text-gray-400"/>
            </x-action-button>

            <x-action-button :if="$shouldQueue" :action="uri([PostsController::class, 'queue'], post: $pendingPost->id)" target="#pending-posts">
                <x-icon name="lucide:alarm-clock-check" class="size-6 sm:size-5 text-gray-400"/>
            </x-action-button>
        </div>
    </div>

    <div class="flex justify-between">
        <div class="bg-gray-200 p-2 text-xs rounded-lg shadow-xs" :if="$pendingCount">
            {{ $pendingCount }} pending
        </div>
        <div class="bg-gray-200 p-2 text-xs rounded-lg shadow-xs" :if="$futureQueued">
            {{ $futureQueued }} queued
        </div>
    </div>
</div>

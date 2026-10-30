<?php

use App\Todo\TodoController;

use function Tempest\Router\uri;

?>

<x-base title="Todo" :footer="false">
    <x-container>
        <nav class="flex gap-2 mb-4" :if="$workspaces !== []">
            <a
                :foreach="$workspaces as $item"
                :href="uri([TodoController::class, 'show'], workspaceId: $item->id)"
                class="px-3 py-1 rounded-md font-bold"
                :class="$item->id->equals($workspace->id) ? 'bg-primary text-white' : 'bg-white text-gray-600 hover:bg-gray-200'"
            >{{ $item->name }}</a>
        </nav>

        <textarea
            name="markdown"
            class="w-full min-h-[80vh] p-4 bg-white rounded-md shadow-sm font-mono text-sm resize-none focus:outline-none"
            placeholder="# Project name&#10;&#10;- Todo 1&#10;- Todo 2"
            autofocus
        >{{ $markdown }}</textarea>
    </x-container>
</x-base>

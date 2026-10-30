<?php

use App\Todo\TodoController;

use function Tempest\Router\uri;

?>

<!doctype html>
<html lang="en" class="bg-white">
<head>
    <title :if="$workspace && $filters">{{ implode(' ', $filters) }} | {{ $workspace->name }} | Todo</title>
    <title :elseif="$workspace">{{ $workspace->name }} | Todo</title>
    <title :else>Todo</title>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
    <meta name="theme-color" content="#ffffff">

    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">

    <x-vite-tags entrypoint="app/main.entrypoint.css"/>
    <x-vite-tags entrypoint="app/Todo/todo.entrypoint.ts"/>
</head>
<body class="antialiased bg-white text-gray-900">
<nav
    :if="$workspace && (count($workspaces) > 1 || $filters)"
    class="flex flex-wrap justify-end gap-x-4 gap-y-2 px-6 py-4 font-mono text-sm lg:fixed lg:inset-y-0 lg:left-0 lg:w-56 lg:flex-col lg:flex-nowrap lg:justify-start lg:gap-2 lg:px-10 lg:py-24"
>
    <a
        :foreach="$workspaces as $item"
        :href="uri([TodoController::class, 'show'], workspaceId: $item->id)"
        :class="$item->id->equals($workspace->id) ? 'text-gray-900 font-bold' : 'text-gray-400 hover:text-gray-900'"
    >{{ $item->name }}</a>

    <div :if="$filters" class="flex flex-wrap gap-x-4 gap-y-2 lg:flex-col lg:gap-2 lg:mt-6 lg:pt-6 lg:border-t lg:border-gray-200">
        <span>Showing</span>
        <a
            :foreach="$filterLinks as $tag => $removeUri"
            :href="$removeUri"
            :title="'Stop filtering on ' . $tag"
            class="group font-bold"
        >{{ $tag }} <span class="font-normal text-gray-400 group-hover:text-gray-900">×</span></a>
        <a :href="uri([TodoController::class, 'show'], workspaceId: $workspace->id)" class="text-gray-400 hover:text-gray-900">Clear filter</a>
    </div>
</nav>

<main class="px-6 pt-4 pb-24 lg:px-56 lg:pt-24">
    <div class="max-w-[65ch] mx-auto">
        <textarea
            id="todo-markdown"
            name="markdown"
            :data-items="json_encode($items)"
            :data-filter="implode(' ', $filters)"
            :data-filter-url="$workspace ? uri([TodoController::class, 'show'], workspaceId: $workspace->id) : null"
            :data-save-url="$workspace ? uri([TodoController::class, 'save'], workspaceId: $workspace->id) : null"
            :data-complete-project-url="uri([TodoController::class, 'completeProject'], projectId: '__ID__')"
            :data-complete-task-url="uri([TodoController::class, 'completeTask'], taskId: '__ID__')"
            :data-reopen-project-url="uri([TodoController::class, 'reopenProject'], projectId: '__ID__')"
            :data-reopen-task-url="uri([TodoController::class, 'reopenTask'], taskId: '__ID__')"
            class="block w-full min-h-[70vh] font-mono resize-none focus:outline-none"
            placeholder="# Project name&#10;&#10;- Todo 1&#10;- Todo 2"
            autofocus
        >{{ $markdown }}</textarea>
    </div>
</main>

<div id="todo-status" class="fixed bottom-4 right-6 font-mono text-xs text-gray-400" aria-live="polite"></div>
</body>
</html>

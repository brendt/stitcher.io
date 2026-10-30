<?php

use App\Todo\TodoController;
use App\Todo\TodoMode;

use function Tempest\Router\uri;

?>

<!doctype html>
<html lang="en" class="bg-white overflow-x-clip">
<head>
    <title :if="$filters || $project">{{ implode(' ', array_filter([$project, ...$filters])) }} | {{ $title }} | Todo</title>
    <title :elseif="$title">{{ $title }} | Todo</title>
    <title :else>Todo</title>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
    <meta name="theme-color" content="#ffffff">

    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">

    <x-vite-tags entrypoint="app/main.entrypoint.css"/>
    <x-vite-tags entrypoint="app/Todo/todo.entrypoint.ts"/>
</head>
<body class="antialiased bg-white text-gray-900 overflow-x-clip">
<nav
    :if="$workspaces !== []"
    class="sticky top-0 z-20 flex flex-wrap items-center justify-between gap-x-4 gap-y-2 px-6 py-4 bg-white border-b border-gray-200 font-mono text-sm lg:fixed lg:inset-y-0 lg:left-0 lg:w-56 lg:flex-col lg:flex-nowrap lg:items-start lg:justify-start lg:gap-2 lg:px-10 lg:py-24 lg:bg-transparent lg:border-b-0"
>
    <!-- On small screens, a filtered view only shows its filters at the top -->
    <div :class="$filters || $project ? 'hidden lg:contents' : 'contents'">
        <form :foreach="$workspaces as $item" method="post" :action="uri([TodoController::class, 'openWorkspace'])" class="contents">
            <button
                type="submit"
                name="workspace"
                :value="$item->id"
                class="text-left cursor-pointer"
                :class="$workspace?->id->equals($item->id) ? 'text-gray-900 font-bold' : 'text-gray-400 hover:text-gray-900'"
            >{{ $item->name }}</button>
        </form>

        <a
            :href="uri([TodoController::class, 'soon'])"
            class="lg:mt-4"
            :class="$mode === TodoMode::SOON ? 'text-gray-900 font-bold' : 'text-gray-400 hover:text-gray-900'"
        >Soon</a>

        <a
            :href="uri([TodoController::class, 'archived'])"
            :class="$mode === TodoMode::ARCHIVED ? 'text-gray-900 font-bold' : 'text-gray-400 hover:text-gray-900'"
        >Archived</a>
    </div>

    <div :if="$filters || $project" class="flex flex-wrap gap-x-4 gap-y-2 lg:flex-col lg:gap-2 lg:mt-6 lg:pt-6 lg:border-t lg:border-gray-200">
        <span :if="$mode === TodoMode::SOON && $project" class="hidden lg:inline">Showing soon items in</span>
        <span :else class="hidden lg:inline">Showing</span>
        <a
            :if="$project"
            :href="$projectLink"
            :title="'Stop showing only ' . $project"
            class="group font-bold"
        ># {{ $project }} <span class="font-normal text-gray-400 group-hover:text-gray-900">×</span></a>
        <a
            :foreach="$filterLinks as $tag => $removeUri"
            :href="$removeUri"
            :title="'Stop filtering on ' . $tag"
            class="group font-bold"
        >{{ $tag }} <span class="font-normal text-gray-400 group-hover:text-gray-900">×</span></a>
        <a :href="$baseUri" class="text-gray-400 hover:text-gray-900">Clear filter</a>
    </div>
</nav>

<main class="px-6 pt-4 pb-[50vh] lg:px-56 lg:pt-24">
    <div class="max-w-[65ch] mx-auto">
        <textarea
            id="todo-markdown"
            name="markdown"
            :data-items="json_encode($items)"
            :data-tags="json_encode($tags)"
            :data-filter="implode(' ', $filters)"
            :data-project="$project"
            :data-filter-url="$baseUri"
            :data-mode="$mode->value"
            :data-workspace-id="$workspace?->id"
            :data-save-url="$workspace ? uri([TodoController::class, 'save'], workspaceId: $workspace->id) : null"
            :data-complete-project-url="uri([TodoController::class, 'completeProject'], projectId: '__ID__')"
            :data-complete-task-url="uri([TodoController::class, 'completeTask'], taskId: '__ID__')"
            :data-reopen-project-url="uri([TodoController::class, 'reopenProject'], projectId: '__ID__')"
            :data-reopen-task-url="uri([TodoController::class, 'reopenTask'], taskId: '__ID__')"
            :data-archive-project-url="uri([TodoController::class, 'archiveProject'], projectId: '__ID__')"
            :data-unarchive-project-url="uri([TodoController::class, 'unarchiveProject'], projectId: '__ID__')"
            :data-collapse-project-url="uri([TodoController::class, 'collapseProject'], projectId: '__ID__')"
            :data-expand-project-url="uri([TodoController::class, 'expandProject'], projectId: '__ID__')"
            class="block w-full min-h-[70vh] font-mono resize-none focus:outline-none"
            :placeholder="$placeholder"
            autofocus
        >{{ $markdown }}</textarea>

        <form :if="$showAll" method="post" :action="uri([TodoController::class, 'openWorkspace'])" class="mt-12">
            <input type="hidden" name="workspace" :value="$showAll['workspace']">
            <input type="hidden" name="project" :value="$showAll['project']">
            <button type="submit" class="font-mono text-sm text-gray-400 hover:text-gray-900 cursor-pointer">Show all of # {{ $showAll['project'] }} →</button>
        </form>
    </div>
</main>

<div id="todo-status" class="fixed bottom-4 right-6 font-mono text-xs text-gray-400" aria-live="polite"></div>
</body>
</html>

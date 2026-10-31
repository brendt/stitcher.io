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
    :class="$filters || $project ? 'justify-center' : 'justify-between'"
    class="sticky top-0 z-20 flex flex-wrap items-center gap-x-4 gap-y-2 px-6 py-4 bg-white border-b border-gray-200 font-mono text-sm lg:fixed lg:inset-y-0 lg:left-0 lg:w-56 lg:flex-col lg:flex-nowrap lg:items-start lg:justify-start lg:gap-2 lg:px-10 lg:py-24 lg:bg-transparent lg:border-b-0"
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

        <!-- On wide screens, everything is listed in the sidebar -->
        <div class="hidden lg:contents">
            <button type="button" onclick="document.getElementById('new-workspace').showModal()" class="text-left text-gray-400 hover:text-gray-900 cursor-pointer">+ New workspace</button>
            <button :if="$workspace" type="button" onclick="document.getElementById('edit-workspace').showModal()" class="inline-flex items-center gap-1.5 text-left text-gray-400 hover:text-gray-900 cursor-pointer"><svg viewBox="0 0 16 16" class="w-3.5 h-3.5 shrink-0" aria-hidden="true"><path d="M10.5 2.5l3 3L6 13H3v-3l7.5-7.5zM9 4l3 3" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round"/></svg> Edit workspace</button>

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

        <!-- On small screens, only the workspaces fit, the rest is behind a menu -->
        <details class="relative ml-auto lg:hidden">
            <summary aria-label="Menu" class="flex items-center justify-center w-7 h-7 rounded border-[1.5px] border-gray-400 bg-white text-gray-400 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                <svg viewBox="0 0 16 16" class="w-3.5 h-3.5" aria-hidden="true"><path d="M2.5 4h11M2.5 8h11M2.5 12h11" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>
            </summary>

            <div class="absolute right-0 top-full z-30 mt-2 flex flex-col gap-3 min-w-48 p-4 bg-white border border-gray-200 rounded-lg shadow-lg">
                <a
                    :href="uri([TodoController::class, 'soon'])"
                    :class="$mode === TodoMode::SOON ? 'text-gray-900 font-bold' : 'text-gray-600'"
                >Soon</a>

                <a
                    :href="uri([TodoController::class, 'archived'])"
                    :class="$mode === TodoMode::ARCHIVED ? 'text-gray-900 font-bold' : 'text-gray-600'"
                >Archived</a>

                <button type="button" onclick="this.closest('details').open = false; document.getElementById('new-workspace').showModal()" class="text-left text-gray-600 cursor-pointer">+ New workspace</button>
                <button :if="$workspace" type="button" onclick="this.closest('details').open = false; document.getElementById('edit-workspace').showModal()" class="inline-flex items-center gap-1.5 text-left text-gray-600 cursor-pointer"><svg viewBox="0 0 16 16" class="w-3.5 h-3.5 shrink-0" aria-hidden="true"><path d="M10.5 2.5l3 3L6 13H3v-3l7.5-7.5zM9 4l3 3" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round"/></svg> Edit workspace</button>
            </div>
        </details>
    </div>

    <!-- On small screens, the active filters are shown at the top, on wide screens they're shown above the editor -->
    <div :if="$filters || $project" class="contents lg:hidden">
        <x-todo-filters :links="$activeFilterLinks" />
    </div>
</nav>

<main class="px-6 pt-4 pb-[50vh] lg:px-56 lg:pt-24">
    <div :if="$filters || $project" class="hidden sticky top-0 z-20 -mt-20 mb-8 py-6 bg-white font-mono text-sm lg:block">
        <x-todo-filters :links="$activeFilterLinks" />
    </div>

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

<dialog id="new-workspace" class="m-auto w-[min(24rem,calc(100%-3rem))] p-6 rounded-lg border border-gray-200 shadow-xl font-mono text-sm text-gray-900 backdrop:bg-black/20">
    <form method="post" :action="uri([TodoController::class, 'createWorkspace'])" class="flex flex-col gap-4">
        <label for="new-workspace-name" class="font-bold">New workspace</label>
        <input
            id="new-workspace-name"
            type="text"
            name="name"
            required
            autofocus
            autocomplete="off"
            maxlength="255"
            placeholder="Name"
            class="w-full px-3 py-2 rounded border-[1.5px] border-gray-300 focus:border-gray-900 focus:outline-none"
        />
        <div class="flex justify-end gap-2">
            <button type="button" onclick="this.closest('dialog').close()" class="px-3 py-2 rounded border-[1.5px] border-gray-300 text-gray-600 hover:border-gray-900 hover:text-gray-900 cursor-pointer">Cancel</button>
            <button type="submit" class="px-3 py-2 rounded border-[1.5px] border-gray-900 bg-gray-900 text-white cursor-pointer">Create</button>
        </div>
    </form>
</dialog>

<dialog :if="$workspace" id="edit-workspace" class="m-auto w-[min(24rem,calc(100%-3rem))] p-6 rounded-lg border border-gray-200 shadow-xl font-mono text-sm text-gray-900 backdrop:bg-black/20">
    <form method="post" :action="uri([TodoController::class, 'renameWorkspace'])" class="flex flex-col gap-4">
        <label for="edit-workspace-name" class="font-bold">Edit workspace</label>
        <input type="hidden" name="workspace" :value="$workspace->id">
        <input
            id="edit-workspace-name"
            type="text"
            name="name"
            required
            autofocus
            autocomplete="off"
            maxlength="255"
            :value="$workspace->name"
            class="w-full px-3 py-2 rounded border-[1.5px] border-gray-300 focus:border-gray-900 focus:outline-none"
        />
        <div class="flex justify-end gap-2">
            <button type="button" onclick="this.closest('dialog').close()" class="px-3 py-2 rounded border-[1.5px] border-gray-300 text-gray-600 hover:border-gray-900 hover:text-gray-900 cursor-pointer">Cancel</button>
            <button type="submit" class="px-3 py-2 rounded border-[1.5px] border-gray-900 bg-gray-900 text-white cursor-pointer">Save</button>
        </div>
    </form>
</dialog>

<!-- On touch screens, tasks can be added with this button, without having to edit the text, see `todo.entrypoint.ts` -->
<button
    id="todo-add"
    type="button"
    hidden
    aria-label="Add task"
    class="fixed right-6 bottom-[calc(1.5rem+env(safe-area-inset-bottom))] z-30 flex items-center justify-center w-12 h-12 rounded-full border-[1.5px] border-gray-900 bg-white text-gray-900 shadow-lg cursor-pointer"
><svg viewBox="0 0 16 16" class="w-5 h-5" aria-hidden="true"><path d="M8 3v10M3 8h10" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></button>

<dialog id="new-task" class="m-auto w-[min(24rem,calc(100%-3rem))] p-6 rounded-lg border border-gray-200 shadow-xl font-mono text-sm text-gray-900 backdrop:bg-black/20">
    <form class="flex flex-col gap-4">
        <p id="new-task-label" class="font-bold">New task</p>
        <!-- The task is written in an editor, which is added here, see `todo.entrypoint.ts` -->
        <div id="new-task-text" class="w-full px-3 py-2 rounded border-[1.5px] border-gray-300 focus-within:border-gray-900"></div>
        <!-- Buttons for `#` and `@`, and for `#today`, `#tomorrow`, and `#nextweek`, are added here, see `todo.entrypoint.ts`. The row scrolls sideways when they don't fit. -->
        <div id="new-task-tags" class="flex gap-2 -mx-6 px-6 overflow-x-auto [scrollbar-width:none]"></div>
        <div class="relative">
            <select
                id="new-task-project"
                name="project"
                required
                aria-label="Project"
                class="w-full appearance-none pl-3 pr-9 py-2 rounded border-[1.5px] border-gray-300 bg-white text-gray-900 focus:border-gray-900 focus:outline-none"
            ></select>
            <svg viewBox="0 0 16 16" class="pointer-events-none absolute right-3 top-1/2 w-3.5 h-3.5 -translate-y-1/2 text-gray-400" aria-hidden="true"><path d="M4 6l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round"/></svg>
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="this.closest('dialog').close()" class="px-3 py-2 rounded border-[1.5px] border-gray-300 text-gray-600 hover:border-gray-900 hover:text-gray-900 cursor-pointer">Cancel</button>
            <button type="submit" class="px-3 py-2 rounded border-[1.5px] border-gray-900 bg-gray-900 text-white cursor-pointer">Add</button>
        </div>
    </form>
</dialog>

<!-- On touch screens, the editor is read-only until editing is turned on with this button, see `todo.entrypoint.ts` -->
<button
    id="todo-edit"
    type="button"
    hidden
    aria-label="Edit"
    aria-pressed="false"
    class="fixed left-6 bottom-[calc(1.5rem+env(safe-area-inset-bottom))] z-30 flex items-center justify-center w-12 h-12 rounded-full border-[1.5px] border-gray-900 bg-white text-gray-900 shadow-lg cursor-pointer aria-pressed:bg-gray-900 aria-pressed:text-white"
><svg viewBox="0 0 16 16" class="w-5 h-5" aria-hidden="true"><path d="M10.5 2.5l3 3L6 13H3v-3l7.5-7.5zM9 4l3 3" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round"/></svg></button>

<div id="todo-status" class="fixed bottom-4 right-6 font-mono text-xs text-gray-400 pointer-coarse:right-22 pointer-coarse:bottom-[calc(2.75rem+env(safe-area-inset-bottom))]" aria-live="polite"></div>
</body>
</html>

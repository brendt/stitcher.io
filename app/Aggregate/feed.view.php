<?php

use App\Aggregate\Suggestions\SuggestionController;

use function Tempest\Router\uri;

?>

<x-base title="Feed">
    <x-slot name="head">
        <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.6/dist/htmx.min.js" integrity="sha384-Akqfrbj/HpNVo8k11SXBb6TlBWmXXlYQrCSqEWmyKJe+hDm3Z/B2WVG4smwBkRVm" crossorigin="anonymous"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                let copyUris = document.querySelectorAll('.copy-uri');

                copyUris.forEach(element => {
                    element.addEventListener('click', () => {
                        if (navigator.clipboard) {
                            navigator.clipboard.writeText(element.getAttribute('data-uri'));
                        }

                        copyUris.forEach(elementToClear => {
                            elementToClear.classList.remove('copied');
                        })

                        element.classList.add('copied');
                    });
                })
            });

            document.addEventListener('htmx:afterRequest', (event) => {
                if (!event.detail.elt.closest('#search-controls')) return;

                const controls = document.getElementById('search-controls');
                const q = controls.querySelector('[name="q"]').value;
                const sort = controls.querySelector('[name="sort"]').value;

                const params = new URLSearchParams();
                if (q) params.set('q', q);
                if (sort !== 'recent') params.set('sort', sort);

                const qs = params.toString();
                history.replaceState(null, '', '/feed/' + (qs ? '?' + qs : ''));
            });
        </script>
    </x-slot>

    <div class="max-w-[800px] m-auto grid gap-2">
        <x-menu />

        <div class="grid gap-2 mt-4" :if="$user?->isAdmin">
            <x-suggestions :suggestions="$suggestions" />
            <x-pending-posts :pendingPosts="$pendingPosts" :shouldQueue="$shouldQueue" :futureQueued="$futureQueued"/>
        </div>


        <x-card :if="$success ?? null">
            <p>
                <span class="text-emerald-700 font-bold">Your suggestion has been added!</span>&nbsp;<a :href="uri([SuggestionController::class, 'suggest'])" class="font-bold underline hover:no-underline">Add another one</a>
            </p>
        </x-card>

        <x-card :if="! $user?->isAdmin">
            <p>This is my community-driven content aggregator, aka <span class="font-bold">Stitcher's Community Feed</span>. This is a hand-curated list of thought-provoking content from around the web. You can browse through the most recent posts on this page, or you can <a href="/feed/rss">follow the RSS feed</a> directly.</p>

            <p>
                Finally, you can <a :href="uri([SuggestionController::class, 'suggest'])">add your own suggestions</a> as well.
            </p>
        </x-card>

        <div id="search-controls" class="flex gap-2">
            <input
                    type="search"
                    name="q"
                    placeholder="Search..."
                    :value="$q ?? ''"
                    class="grow p-2 px-4 rounded-xs bg-white shadow-sm border-0 outline-none"
                    hx-get="/feed/search"
                    hx-trigger="input changed delay:300ms, search"
                    hx-target="#feed-posts"
                    hx-swap="outerHTML"
                    hx-include="#search-controls"
            />
            <div class="relative">
            <select
                    name="sort"
                    class="appearance-none p-2 pl-4 pr-8 rounded-xs bg-white shadow-sm border-0 outline-none cursor-pointer"
                    hx-get="/feed/search"
                    hx-trigger="change"
                    hx-target="#feed-posts"
                    hx-swap="outerHTML"
                    hx-include="#search-controls"
            >
                <option value="top" :selected="($sort ?? 'recent') === 'top'">Top</option>
                <option value="recent" :selected="($sort ?? 'recent') === 'recent'">Most recent</option>
                <option value="oldest" :selected="($sort ?? 'recent') === 'oldest'">Oldest</option>
            </select>
            <x-icon name="lucide:chevron-down" class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 size-4 text-gray-400" />
            </div>
        </div>

        <x-feed-posts :posts="$posts" :color="$color" />
    </div>
</x-base>

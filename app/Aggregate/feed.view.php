<?php

use function Tempest\Router\uri;
use App\Aggregate\Posts\PostsController;
use App\Aggregate\FeedController;
use Tempest\DateTime\DateTime;
use App\Support\Authentication\AuthController;
use App\Aggregate\Suggestions\SuggestionController;
?>

<x-base title="Feed">
    <x-slot name="head">
        <script :if="$user?->isAdmin" src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.6/dist/htmx.min.js" integrity="sha384-Akqfrbj/HpNVo8k11SXBb6TlBWmXXlYQrCSqEWmyKJe+hDm3Z/B2WVG4smwBkRVm" crossorigin="anonymous"></script>
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
                    <span class="text-sm block overflow-hidden">{{ $post->source->shortName }}</span>
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
</x-base>

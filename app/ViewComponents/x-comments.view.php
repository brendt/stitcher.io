<?php

use function Tempest\Router\uri;
use App\Authentication\AuthController;
use App\Blog\BlogController;
use App\Blog\CommentsController;

$user ??= null;
$comments ??= [];
$confirm ??= null;
?>

<div id="comments" class="grid gap-4">
    <div class="grid gap-2" :if="$user === null">
        <div>
            Login to comment
        </div>
        <div class="flex justify-start gap-2">
            <a :href="uri([AuthController::class, 'google'])" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-xl">
                <x-icon name="logos:google-icon" class="size-6"/>
            </a>
            <a :href="uri([AuthController::class, 'google'])" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-xl">
                <x-icon name="logos:github-icon" class="size-6"/>
            </a>
            <a :href="uri([AuthController::class, 'google'])" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-xl">
                <x-icon name="logos:discord-icon" class="size-6"/>
            </a>
        </div>
    </div>
    <form :else :hx-post="uri([CommentsController::class, 'comment'], slug: $post->slug)" hx-target="#comments" class="grid gap-2">
        <x-input name="comment" label="Leave a comment:" type="textarea" required></x-input>
        <div :if="$commentError ?? null" class="text-red-500">
            {{ $commentError }}
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-pastel rounded-full p-2 px-4 hover:bg-primary hover:text-white font-bold cursor-pointer">
                Submit
            </button>
        </div>
    </form>

    <div class="grid gap-2">
        <div :foreach="$comments as $comment" class="bg-gray-100 p-4 pb-3 rounded-sm">
            <x-markdown class="grid gap-2" :content="$comment->content"/>

            <div class="text-sm flex justify-between flex-wrap">
                <span>Written by {{ $comment->user->name }} on {{ $comment->createdAt->format('YYYY-MM-dd') }}</span>
                <button
                        :if="$user?->owns($comment) && ($deleting ?? null) === $comment->id->value"
                        :hx-post="uri([CommentsController::class, 'delete'], slug: $post->slug, id: $comment->id)"
                        hx-target="#comments"
                        type="button"
                        class="text-red-600 font-bold underline cursor-pointer hover:no-underline">Confirm delete</button>
                <button
                        :elseif="$user?->owns($comment)"
                        :hx-post="uri([CommentsController::class, 'delete'], slug: $post->slug, id: $comment->id)"
                        hx-target="#comments"
                        type="button"
                        class=" underline cursor-pointer hover:no-underline">Delete</button>
            </div>
        </div>
        <div :forelse class="flex justify-center font-bold">
            No comments yet, be the first!
        </div>
    </div>
</div>

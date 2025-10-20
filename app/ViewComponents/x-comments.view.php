<?php

use function Tempest\Router\uri;
use App\Authentication\AuthController;
use App\Blog\BlogController;
use App\Blog\CommentsController;

$user ??= null;
$comments ??= [];
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
        </div>
    </div>
    <form :else :hx-post="uri([CommentsController::class, 'comment'], slug: $post->slug)" hx-target="#comments">
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
        <div :foreach="$comments as $comment" class="bg-gray-100 p-4 rounded-sm">
            <x-markdown class="grid gap-2" :content="$comment->content"/>

            <div class="text-sm">
                Written by {{ $comment->user->name }} on {{ $comment->createdAt->format('YYYY-MM-dd') }}
            </div>
        </div>
        <div :forelse>
            No comments yet
        </div>
    </div>
</div>

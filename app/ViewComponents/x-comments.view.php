<?php

use Tempest\Http\Request;
use function Tempest\get;
use function Tempest\Router\uri;
use App\Authentication\AuthController;
use App\Blog\BlogController;
use App\Blog\CommentsController;
use function Tempest\Support\str;

$user ??= null;
$comments ??= [];
$confirm ??= null;
$back ??= str(get(Request::class)->path)->beforeLast('/comments')->toString();
?>

<div id="comments" class="grid gap-4">

    <div :if="($initial ?? null)" class="grid gap-2 bg-gray-100  justify-center p-4 rounded">
        <div class="text-center">
            Loading…
        </div>
    </div>

    <div :elseif="$user === null" class="grid gap-2 bg-gray-100  justify-center p-4 rounded">
        <div class="text-center">
            Login to comment
        </div>
        <div class="flex justify-start gap-4">
            <a :href="uri([AuthController::class, 'auth'], type: 'google', back: $back)" class="p-2 bg-gray-200 hover:bg-gray-300 rounded-xl">
                <x-icon name="logos:google-icon" class="size-6"/>
            </a>
            <a :href="uri([AuthController::class, 'auth'], type: 'github', back: $back)" class="p-2 bg-gray-200 hover:bg-gray-300 rounded-xl">
                <x-icon name="logos:github-icon" class="size-6"/>
            </a>
            <a :href="uri([AuthController::class, 'auth'], type: 'discord', back: $back)" class="p-2 bg-gray-200 hover:bg-gray-300 rounded-xl">
                <x-icon name="logos:discord-icon" class="size-6"/>
            </a>
        </div>
    </div>

    <form :else :hx-post="uri([CommentsController::class, 'comment'], slug: $post->slug)" hx-target="#comments" class="grid gap-2 bg-gray-100 p-4 rounded">
        <x-input name="comment" label="Leave a comment:" type="textarea" required></x-input>
        <div :if="$commentError ?? null" class="text-red-500">
            {{ $commentError }}
        </div>

        <div class="flex justify-end">
            <button type="submit" class="text-sm bg-pastel rounded-full p-2 px-4 hover:bg-primary hover:text-white font-bold cursor-pointer border-2 border-primary text-primary">
                Submit
            </button>
        </div>
    </form>

    <div class="grid gap-2">
        <div :foreach="$comments as $comment" :id="$comment->anchor" class="bg-gray-100 p-4 pb-3 rounded-sm overflow-auto">
            <x-markdown class="grid gap-2" :content="$comment->content"/>

            <div class="text-sm flex justify-between flex-wrap border-t border-gray-200 pt-2 mt-2">
                <span>Written by {{ $comment->user->name }} on {{ $comment->createdAt->format('YYYY-MM-dd') }}</span>
                <button
                        :if="$user?->owns($comment) && ($deleting ?? null) === $comment->id->value"
                        :hx-post="uri([CommentsController::class, 'delete'], slug: $post->slug, id: $comment->id)"
                        hx-target="#comments"
                        type="button"
                        class="text-red-600 font-bold underline cursor-pointer hover:no-underline">Confirm delete
                </button>
                <button
                        :elseif="$user?->owns($comment)"
                        :hx-post="uri([CommentsController::class, 'delete'], slug: $post->slug, id: $comment->id)"
                        hx-target="#comments"
                        type="button"
                        class=" underline cursor-pointer hover:no-underline">Delete
                </button>
            </div>
        </div>
        <div :forelse class="flex justify-center font-bold">
            No comments yet, be the first!
        </div>
    </div>
</div>

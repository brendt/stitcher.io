<?php

use App\Dungeon\Http\DungeonHomeController;
use function Tempest\Router\uri;
use App\Support\Authentication\AuthController;
?>

<x-dungeon>
    <?php $back = uri([DungeonHomeController::class, 'index']); ?>
    <div class="flex flex-col items-center justify-center min-h-screen gap-4">
        <div class="bg-gray-900 border-gray-500 border-4 p-12 rounded-xl shadow-2xl flex flex-col items-center gap-4">
            <h1 class="title text-2xl text-gray-200">Dungeon | Login</h1>

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
    </div>
</x-dungeon>
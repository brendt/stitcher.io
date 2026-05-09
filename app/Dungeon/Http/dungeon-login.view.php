<?php

use App\Dungeon\Http\DungeonHomeController;
use function Tempest\Router\uri;
use App\Support\Authentication\AuthController;
?>

<x-dungeon>
    <?php $back = uri([DungeonHomeController::class, 'index']); ?>
    <div class="flex flex-col items-center justify-center min-h-screen gap-4">
        <div class="bg-gray-950/90 backdrop-blur-md border border-white/10 px-12 py-10 rounded-2xl shadow-2xl flex flex-col items-center gap-6">
            <h1 class="title text-2xl text-gray-200 tracking-wide">Dungeon</h1>

            <div class="w-px bg-white/10 self-stretch"></div>

            <div class="flex flex-col gap-3 w-full">
                <a :href="uri([AuthController::class, 'auth'], type: 'google', back: $back)" class="title flex items-center justify-center gap-3 bg-amber-800 border-2 border-amber-600 hover:bg-amber-700 hover:border-amber-500 px-6 py-3 rounded-xl shadow-lg shadow-amber-950/60 text-amber-100 hover:text-white transition-all">
                    <x-icon name="uim:google" class="size-5"/>
                    Google
                </a>
                <a :href="uri([AuthController::class, 'auth'], type: 'github', back: $back)" class="title flex items-center justify-center gap-3 bg-amber-800 border-2 border-amber-600 hover:bg-amber-700 hover:border-amber-500 px-6 py-3 rounded-xl shadow-lg shadow-amber-950/60 text-amber-100 hover:text-white transition-all">
                    <x-icon name="uim:github" class="size-5"/>
                    GitHub
                </a>
                <a :href="uri([AuthController::class, 'auth'], type: 'discord', back: $back)" class="title flex items-center justify-center gap-3 bg-amber-800 border-2 border-amber-600 hover:bg-amber-700 hover:border-amber-500 px-6 py-3 rounded-xl shadow-lg shadow-amber-950/60 text-amber-100 hover:text-white transition-all">
                    <x-icon name="uim:discord" class="size-5"/>
                    Discord
                </a>
            </div>
        </div>
    </div>
</x-dungeon>
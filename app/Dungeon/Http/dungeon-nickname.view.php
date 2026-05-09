<?php

use function Tempest\Router\uri;
use App\Dungeon\Http\DungeonHomeController;

?>

<x-dungeon>
    <div class="flex flex-col items-center justify-center min-h-screen gap-4 p-4">
        <div class="bg-gray-950/90 backdrop-blur-md border border-white/10 px-12 py-10 rounded-2xl shadow-2xl flex flex-col items-center gap-6">
            <h1 class="title text-2xl text-gray-200 tracking-wide">Choose your nickname</h1>

            <div class="w-px bg-white/10 self-stretch"></div>

            <form method="POST" :action="uri([DungeonHomeController::class, 'storeNickname'])" class="flex flex-col gap-4 w-full">
                <input
                    type="text"
                    name="nickname"
                    placeholder="Nickname"
                    required
                    autofocus
                    class="bg-gray-900 border border-white/10 text-gray-200 placeholder-gray-600 px-4 py-3 rounded-xl focus:outline-none focus:border-amber-600 transition-colors"
                />
                <button
                    type="submit"
                    class="cursor-pointer title flex items-center justify-center bg-amber-800 border-2 border-amber-600 hover:bg-amber-700 hover:border-amber-500 px-6 py-3 rounded-xl shadow-lg shadow-amber-950/60 text-amber-100 hover:text-white transition-all"
                >
                    Enter the Dungeon
                </button>
            </form>
        </div>
    </div>
</x-dungeon>

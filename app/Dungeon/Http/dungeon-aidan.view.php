<?php

use App\Dungeon\Http\DungeonHomeController;
use function Tempest\Router\uri;
use App\Support\Authentication\AuthController;
?>

<x-dungeon>
    <?php $back = uri([DungeonHomeController::class, 'index']); ?>
    <div class="flex flex-col items-center justify-center min-h-screen gap-4">
        <div class="bg-gray-950/90 backdrop-blur-md border border-white/10 px-12 py-10 rounded-2xl shadow-2xl flex flex-col items-center gap-6">
            <h1 class="title text-2xl text-gray-200 tracking-wide">Nice try, Aidan.</h1>
        </div>
    </div>
</x-dungeon>
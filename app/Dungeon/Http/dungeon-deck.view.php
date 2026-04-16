<?php

use App\Dungeon\Http\DungeonGameController;
use function Tempest\Router\uri;

?>

<x-dungeon>
    <div class="p-4">
        <a :href="uri([DungeonGameController::class, 'new'])" class="title bg-gray-500 border-transparent border-4 hover:bg-gray-600 hover:border-gray-500 p-2">Enter the dungeon</a>
    </div>
</x-dungeon>
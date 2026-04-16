<?php

use App\Dungeon\Http\DungeonGameController;
use function Tempest\Router\uri;

?>

<x-dungeon>
    <div class="grid gap-8 pb-8">
        <div class="sticky flex justify-center">
            <div class="bg-gray-900/70 p-4 shadow-2xl rounded-b-2xl flex gap-16">
                <div class="grid">
                    <span class="title">Tokens</span>
                    <span>{{ $stats->tokens }}</span>
                </div>
                <div class="grid">
                    <span class="title">Coins</span>
                    <span>{{ $stats->coins }}</span>
                </div>
                <div class="grid">
                    <span class="title">Victory Points</span>
                    <span>{{ $stats->victoryPoints }}</span>
                </div>
                <div class="grid">
                    <span class="title">Shards</span>
                    <span>{{ $stats->shards }}</span>
                </div>
            </div>
        </div>

        <div class="flex justify-center">
            <a :href="uri([DungeonGameController::class, 'new'])" class="title bg-gray-500 border-transparent border-4 hover:bg-gray-600 hover:border-gray-500 p-2">Enter the dungeon (costs 1 token)</a>
        </div>

        <div class="container mx-auto grid gap-4 justify-center">
            <div class="bg-gray-900/70 p-8 shadow-2xl rounded-2xl flex-col">
                <h2 class="title">Shop</h2>
                <div class="flex gap-4 flex-wrap">
                    <div :foreach="$shop as $card">
                        <x-dungeon-card :card="$card->card" />
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto grid gap-4">
            <h2 class="title">Deck</h2>
            <div class="flex gap-4 flex-wrap">
                <div :foreach="$deck as $card">
                    <x-dungeon-card :card="$card->card" />
                </div>
            </div>
        </div>
    </div>
</x-dungeon>
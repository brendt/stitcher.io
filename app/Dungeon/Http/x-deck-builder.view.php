<?php
/** @var \App\Dungeon\Persistence\DungeonUserCard[] $deck */

use App\Dungeon\Persistence\DungeonUserCard;
use App\Dungeon\Http\DungeonGameController;
use App\Dungeon\Http\DungeonHomeController;
use function Tempest\Support\arr;
use function Tempest\Router\uri;
use App\Dungeon\Dungeon;

$activeCards = arr($deck)->filter(fn (DungeonUserCard $card) => $card->isActive);
$inactiveCards = arr($deck)->filter(fn (DungeonUserCard $card) => ! $card->isActive);
?>

<div id="deck-builder" class="grid gap-8">
    <div class="sticky flex justify-center">
        <div class="bg-gray-900/70 p-4 shadow-2xl rounded-b-2xl flex gap-16">
            <div class="grid">
                <span class="title">Level</span>
                <span>{{ $stats->level->getName() }} <span class="text-sm">({{ $stats->experience }}&thinsp;xp)</span></span>
            </div>
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
        <a :if="$stats->tokens > 1" :href="uri([DungeonGameController::class, 'new'])" class="title bg-gray-500 border-transparent border-4 hover:bg-gray-600 hover:border-gray-500 p-2">Enter the dungeon (costs 1 token)</a>
        <div :else class="title text-red-200">No more tokens, come back tomorrow</div>
    </div>


    <div class="container mx-auto grid gap-4 justify-center">
        <div class="bg-gray-900/70 p-8 pt-4 shadow-2xl rounded-2xl grid gap-2" :if="count($shop) > 0">
            <h2 class="title text-center">Shop</h2>
            <div class="flex gap-4 flex-wrap">
                <div
                        :foreach="$shop as $card"
                        :if="$stats->canBuy($card)"
                        hx-trigger="click"
                        :hx-post="uri([DungeonHomeController::class, 'buyCard'], id: $card->id)"
                        hx-target="#deck-builder"
                        hx-swap="outerHTML"
                >
                    <x-dungeon-card :card="$card->card" :include-level="! $stats->level->hasAccessTo($card->card->level)" :price="$card->price"/>
                </div>
                <div :else>
                    <x-dungeon-card :card="$card->card" :disabled :include-level="! $stats->level->hasAccessTo($card->card->level)" :price="$card->price"/>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-center gap-8" >
        <div class="flex flex-wrap items-start content-start gap-2 justify-end w-1/2 bg-gray-900/70 p-8 pt-4 shadow-2xl rounded-2xl">
            <h2 class="title w-full">Available Cards</h2>
            <div class="flex gap-4 flex-wrap">
                <div
                        :foreach="$inactiveCards as $card"
                        hx-trigger="click"
                        :hx-post="uri([DungeonHomeController::class, 'activateCard'], id: $card->id)"
                        hx-target="#deck-builder"
                        hx-swap="outerHTML"
                >
                    <x-dungeon-card :card="$card->card"/>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-start content-start gap-2 justify-start w-1/2">
            <h2 class="title w-full">Hand ({{ $activeCards->count() }} / {{ Dungeon::MAX_HAND_COUNT }})</h2>
            <div class="flex gap-4 flex-wrap">
                <div
                        :foreach="$activeCards as $card"
                        hx-trigger="click"
                        :hx-post="uri([DungeonHomeController::class, 'deactivateCard'], id: $card->id)"
                        hx-target="#deck-builder"
                        hx-swap="outerHTML"
                >
                    <x-dungeon-card :card="$card->card"/>
                </div>
            </div>
        </div>
    </div>
</div>
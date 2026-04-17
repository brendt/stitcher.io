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

    {{-- Stats HUD --}}
    <div class="sticky top-0 z-20 flex justify-center">
        <div class="bg-gray-950/90 backdrop-blur-md border-x border-b border-white/10 px-8 py-3 shadow-2xl rounded-b-2xl flex gap-8">
            <div class="flex flex-col items-center gap-0.5">
                <span class="title text-xs text-gray-500 uppercase tracking-widest">Level</span>
                <span class="text-sm text-gray-200">{{ $stats->level->getName() }} <span class="text-xs text-gray-500">({{ $stats->experience }}&thinsp;xp)</span></span>
            </div>
            <div class="w-px bg-white/10"></div>
            <div class="flex flex-col items-center gap-0.5">
                <span class="title text-xs text-gray-500 uppercase tracking-widest">Tokens</span>
                <span class="text-sm text-gray-200">{{ $stats->tokens }}</span>
            </div>
            <div class="w-px bg-white/10"></div>
            <div class="flex flex-col items-center gap-0.5">
                <span class="title text-xs text-gray-500 uppercase tracking-widest">Coins</span>
                <span class="text-sm text-gray-200">{{ $stats->coins }}</span>
            </div>
            <div class="w-px bg-white/10"></div>
            <div class="flex flex-col items-center gap-0.5">
                <span class="title text-xs text-gray-500 uppercase tracking-widest">Victory Points</span>
                <span class="text-sm text-gray-200">{{ $stats->victoryPoints }}</span>
            </div>
            <div class="w-px bg-white/10"></div>
            <div class="flex flex-col items-center gap-0.5">
                <span class="title text-xs text-gray-500 uppercase tracking-widest">Shards</span>
                <span class="text-sm text-gray-200">{{ $stats->shards }}</span>
            </div>
        </div>
    </div>

    {{-- Enter Dungeon CTA --}}
    <div class="flex justify-center">
        <a :if="$stats->tokens > 1"
           :href="uri([DungeonGameController::class, 'new'])"
           class="title bg-amber-800 border-2 border-amber-600 hover:bg-amber-700 hover:border-amber-500 px-8 py-3 rounded-xl shadow-lg shadow-amber-950/60 text-amber-100 hover:text-white transition-all text-lg">
            Enter the Dungeon
            <span class="text-sm text-amber-400 ml-2">(costs 1 token)</span>
        </a>
        <div :else class="title text-red-400/80 text-sm bg-red-950/30 border border-red-900/40 px-6 py-3 rounded-xl">
            No more tokens — come back tomorrow
        </div>
    </div>

    {{-- Shop --}}
    <div class="container mx-auto grid gap-4 justify-center" :if="count($shop) > 0">
        <div class="bg-gray-900/70 px-8 pb-8 pt-4 shadow-2xl rounded-2xl border border-white/5 grid gap-3">
            <h2 class="title text-center text-lg tracking-wide">Shop</h2>
            <div class="flex gap-4 flex-wrap justify-center">
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

    {{-- Deck Builder: Available Cards + Hand --}}
    <div class="flex justify-center gap-6 px-4">
        <div class="flex flex-col gap-3 w-1/2 bg-gray-900/70 px-6 pb-8 pt-4 shadow-2xl rounded-2xl border border-white/5">
            <h2 class="title text-center text-base tracking-wide text-gray-300">Available Cards</h2>
            <div class="flex gap-4 flex-wrap justify-center">
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

        <div class="flex flex-col gap-3 w-1/2 bg-gray-900/70 px-6 pb-8 pt-4 shadow-2xl rounded-2xl border border-white/5">
            <h2 class="title text-center text-base tracking-wide text-gray-300">
                Hand
                <span class="text-sm text-gray-500 ml-1">({{ $activeCards->count() }}&thinsp;/&thinsp;{{ Dungeon::MAX_HAND_COUNT }})</span>
            </h2>
            <div class="flex gap-4 flex-wrap justify-center">
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

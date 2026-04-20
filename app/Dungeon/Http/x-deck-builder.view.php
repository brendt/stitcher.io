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

<style>
    #enter-dungeon-overlay {
        position: fixed;
        inset: 0;
        z-index: 9000;
        background: rgba(0, 0, 0, 0.88);
        display: none;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 1.5rem;
    }

    #enter-dungeon-overlay.active {
        display: flex;
    }

    .enter-dungeon-spinner {
        width: 48px;
        height: 48px;
        border: 3px solid rgba(217, 119, 6, 0.2);
        border-top-color: rgb(217, 119, 6);
        border-radius: 50%;
        animation: dungeon-spin 0.8s linear infinite;
    }

    @keyframes dungeon-spin {
        to { transform: rotate(360deg); }
    }

    .enter-dungeon-overlay-text {
        font-family: var(--font-title, serif);
        color: rgb(252, 211, 77);
        font-size: 1.125rem;
        letter-spacing: 0.05em;
    }
</style>

<div id="enter-dungeon-overlay" aria-live="polite" aria-label="Loading dungeon…">
    <div class="enter-dungeon-spinner"></div>
    <div class="enter-dungeon-overlay-text">Entering the Dungeon…</div>
</div>

<div id="deck-builder" class="grid gap-8">

    {{-- Stats HUD --}}
    <div class="sticky top-0 z-20 flex justify-center">
        <div class="bg-gray-950/90 backdrop-blur-md border-x border-b border-white/10 px-4 sm:px-8 py-3 shadow-2xl rounded-b-2xl flex flex-wrap justify-center gap-y-2 gap-x-4 sm:gap-x-8">
            <a :href="uri([DungeonHomeController::class, 'leaderboard'])" class="flex flex-col items-center gap-0.5 px-2 sm:px-0 group">
                <span class="title text-xs text-gray-200 tracking-wide group-hover:text-amber-300 transition-colors">{{ $stats->nickname }}</span>
                <span class="title text-xs bg-amber-800/60 border border-amber-600/60 rounded-full px-2 py-0.5 text-amber-300 group-hover:bg-amber-800 group-hover:border-amber-600 transition-all">#{{ $rank }}</span>
            </a>
            <div class="w-px bg-white/10 hidden sm:block"></div>
            <div class="flex flex-col items-center gap-0.5 px-2 sm:px-0">
                <span class="title text-xs text-gray-500 uppercase tracking-widest">Level</span>
                <span class="text-sm text-gray-200">{{ $stats->level->getName() }} <span :if="$stats->level->nextLevel()" class="text-xs text-gray-500">({{ $stats->experience }}&thinsp;/&thinsp;{{ $stats->level->nextMilestone() }})</span></span>
            </div>
            <div class="w-px bg-white/10 hidden sm:block"></div>
            <div class="flex flex-col items-center gap-0.5 px-2 sm:px-0">
                <span class="title text-xs text-gray-500 uppercase tracking-widest">Tokens</span>
                <span class="text-sm text-gray-200">{{ $stats->tokens }}</span>
            </div>
            <div class="w-px bg-white/10 hidden sm:block"></div>
            <div class="flex flex-col items-center gap-0.5 px-2 sm:px-0">
                <span class="title text-xs text-gray-500 uppercase tracking-widest">Coins</span>
                <span class="text-sm text-gray-200">{{ number_format($stats->coins) }}</span>
            </div>
            <div class="w-px bg-white/10 hidden sm:block"></div>
            <div class="flex flex-col items-center gap-0.5 px-2 sm:px-0">
                <span class="title text-xs text-gray-500 uppercase tracking-widest">Victory Points</span>
                <span class="text-sm text-gray-200">{{ number_format($stats->victoryPoints) }}</span>
            </div>
            <div class="w-px bg-white/10 hidden sm:block"></div>
            <div class="flex flex-col items-center gap-0.5 px-2 sm:px-0">
                <span class="title text-xs text-gray-500 uppercase tracking-widest">Shards</span>
                <span class="text-sm text-gray-200">{{ number_format($stats->shards) }}</span>
            </div>
        </div>
    </div>

    {{-- Enter Dungeon CTA --}}
    <div class="flex justify-center px-4">
        <a
                :if="$dungeon?->hasEnded === false"
                :href="uri([DungeonGameController::class, 'dungeon'])"
                class="title bg-amber-800 border-2 border-amber-600 hover:bg-amber-700 hover:border-amber-500 px-8 py-3 rounded-xl shadow-lg shadow-amber-950/60 text-amber-100 hover:text-white transition-all text-lg text-center"
            >
            Continue your run
        </a>
        <button :elseif="$stats->tokens >= 1"
                id="enter-dungeon-btn"
                data-href="{{ uri([DungeonGameController::class, 'new']) }}"
                type="button"
                class="title cursor-pointer bg-amber-800 border-2 border-amber-600 hover:bg-amber-700 hover:border-amber-500 px-8 py-3 rounded-xl shadow-lg shadow-amber-950/60 text-amber-100 hover:text-white transition-all text-lg text-center">
            Enter the Dungeon
            <span class="text-sm text-amber-400 ml-2 sm:inline block">(costs 1 token)</span>
        </button>
        <div :else class="flex flex-col items-center gap-1">
            <div class="title bg-amber-800/20 border-2 border-amber-700/25 px-8 py-3 rounded-xl text-amber-200/40 text-lg cursor-not-allowed select-none">
                Enter the Dungeon
            </div>
            <span class="text-xs text-gray-500 tracking-wide">No more tokens — come back tomorrow</span>
        </div>
    </div>

    {{-- Shop --}}
    <div class="mx-auto grid gap-4 justify-center px-4" :if="count($shop) > 0">
        <div class="bg-gray-900/70 px-4 sm:px-8 pb-8 pt-4 shadow-2xl rounded-2xl border border-white/5 grid gap-3">
            <h2 class="title text-center text-lg tracking-wide">Shop</h2>
            <div class="flex gap-4 flex-wrap justify-center">
                <div class="card-action" :foreach="$shop as $card" :if="$stats->canBuy($card)">
                    <x-dungeon-card :card="$card->card" :include-level="! $stats->level->hasAccessTo($card->card->level)" :price="$card->price"/>
                    <div class="card-confirm-overlay">
                        <div class="card-confirm-info">
                            <div class="card-confirm-name">{{ $card->card->name }}</div>
                            <div class="card-confirm-description">{{ $card->card->description }}</div>
                        </div>
                        <div class="card-confirm-actions">
                            <button
                                hx-trigger="click"
                                :hx-post="uri([DungeonHomeController::class, 'buyCard'], id: $card->id)"
                                hx-target="#deck-builder"
                                hx-swap="outerHTML"
                                class="card-confirm-btn"
                            >Buy for {{ $card->price }} coins</button>
                            <button type="button" class="card-cancel-btn">Cancel</button>
                        </div>
                    </div>
                </div>
                <div :else>
                    <x-dungeon-card :card="$card->card" :disabled :include-level="! $stats->level->hasAccessTo($card->card->level)" :price="$card->price"/>
                </div>
            </div>
        </div>
    </div>

    {{-- Deck Builder: Available Cards + Hand --}}
    <div class="relative">
        <div :isset="$deckValidationFailed" class="deck-validation-error absolute inset-x-0 -top-4 flex justify-center px-4 z-10">
            <div class="bg-gray-900/70 border border-amber-700/40 px-6 py-3 rounded-2xl shadow-2xl text-center">
                <span class="title text-amber-400 text-sm">{{ $deckValidationFailed->message }}</span>
            </div>
        </div>

        <div class="flex flex-col md:flex-row justify-center gap-6 px-4">
        <div class="flex flex-col gap-3 w-full md:w-1/2 bg-gray-900/70 px-4 sm:px-6 pb-8 pt-4 shadow-2xl rounded-2xl border border-white/5">
            <h2 class="title text-center text-base tracking-wide text-gray-300">Available Cards</h2>
            <div class="flex gap-4 flex-wrap justify-center">
                <div class="card-action" :foreach="$inactiveCards as $card">
                    <x-dungeon-card :card="$card->card"/>
                    <div class="card-confirm-overlay">
                        <div class="card-confirm-info">
                            <div class="card-confirm-name">{{ $card->card->name }}</div>
                            <div class="card-confirm-description">{{ $card->card->description }}</div>
                        </div>
                        <div class="card-confirm-actions">
                            <button
                                hx-trigger="click"
                                :hx-post="uri([DungeonHomeController::class, 'activateCard'], id: $card->id)"
                                hx-target="#deck-builder"
                                hx-swap="outerHTML swap:350ms"
                                class="card-confirm-btn"
                            >Add to deck</button>
                            <button type="button" class="card-cancel-btn">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3 w-full md:w-1/2 bg-gray-900/70 px-4 sm:px-6 pb-8 pt-4 shadow-2xl rounded-2xl border border-white/5">
            <h2 class="title text-center text-base tracking-wide text-gray-300">
                Deck
                <span class="text-sm text-gray-500 ml-1">({{ $activeCards->count() }}&thinsp;/&thinsp;{{ Dungeon::MAX_HAND_COUNT }})</span>
            </h2>
            <div class="flex gap-4 flex-wrap justify-center">
                <div class="card-action" :foreach="$activeCards as $card">
                    <x-dungeon-card :card="$card->card"/>
                    <div class="card-confirm-overlay">
                        <div class="card-confirm-info">
                            <div class="card-confirm-name">{{ $card->card->name }}</div>
                            <div class="card-confirm-description">{{ $card->card->description }}</div>
                        </div>
                        <div class="card-confirm-actions">
                            <button
                                hx-trigger="click"
                                :hx-post="uri([DungeonHomeController::class, 'deactivateCard'], id: $card->id)"
                                hx-target="#deck-builder"
                                hx-swap="outerHTML swap:350ms"
                                class="card-confirm-btn"
                            >Remove from hand</button>
                            <button type="button" class="card-cancel-btn">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

</div>

<script>
if (!window._cardActionListenerAttached) {
    window._cardActionListenerAttached = true;
    document.addEventListener('click', function (e) {
        if (e.target.closest('.card-cancel-btn')) {
            e.target.closest('.card-action').classList.remove('is-pending');
            e.stopPropagation();
            return;
        }

        if (e.target.closest('.card-confirm-btn')) {
            return; // let htmx handle it; DOM will be swapped anyway
        }

        document.querySelectorAll('.card-action.is-pending').forEach(function (el) {
            el.classList.remove('is-pending');
        });

        var action = e.target.closest('.card-action');
        if (action) {
            action.classList.add('is-pending');
        }
    });
}
</script>

<script>
(function () {
    var btn = document.getElementById('enter-dungeon-btn');
    if (!btn) return;

    btn.addEventListener('click', async function () {
        var overlay = document.getElementById('enter-dungeon-overlay');
        var href = btn.dataset.href;

        overlay.classList.add('active');
        btn.disabled = true;

        var minDelay = new Promise(function (resolve) { setTimeout(resolve, 1000); });
        var redirectUrl = fetch(href, { redirect: 'follow' }).then(function (r) { return r.url; });

        var results = await Promise.all([minDelay, redirectUrl]);
        window.location.assign(results[1]);
    });
}());
</script>

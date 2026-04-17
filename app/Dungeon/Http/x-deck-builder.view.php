<?php
/** @var \App\Dungeon\Persistence\DungeonUserCard[] $deck */

use App\Dungeon\Persistence\DungeonUserCard;
use App\Dungeon\Http\DungeonDeckController;
use function Tempest\Support\arr;
use function Tempest\Router\uri;

$activeCards = arr($deck)->filter(fn (DungeonUserCard $card) => $card->isActive);
$inactiveCards = arr($deck)->filter(fn (DungeonUserCard $card) => ! $card->isActive);
?>

<div class="grid grid-cols-2 gap-8" id="deck-builder">
    <div class="grid gap-2 justify-end">
        <h2 class="title">Available Cards</h2>
        <div class="flex gap-4 flex-wrap">
            <div
                    :foreach="$inactiveCards as $card"
                    hx-trigger="click"
                    :hx-post="uri([DungeonDeckController::class, 'activateCard'], id: $card->id)"
                    hx-target="#deck-builder"
                    hx-swap="outerHTML"
            >
                <x-dungeon-card :card="$card->card"/>
            </div>
        </div>
    </div>
    <div class="grid gap-2">
        <h2 class="title">Hand</h2>
        <div class="flex gap-4 flex-wrap">
            <div
                    :foreach="$activeCards as $card"
                    hx-trigger="click"
                    :hx-post="uri([DungeonDeckController::class, 'deactivateCard'], id: $card->id)"
                    hx-target="#deck-builder"
                    hx-swap="outerHTML"
            >
                <x-dungeon-card :card="$card->card"/>
            </div>
        </div>
    </div>
</div>
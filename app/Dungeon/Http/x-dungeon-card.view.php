<?php
/** @var \App\Dungeon\Card */

$class = 'rarity-' . strtolower($card->rarity->name);
if ($disabled ?? false) {
    $class .= ' card-disabled';
}
?>

<div class="card" :class="$class">
    <img :src="'/dungeon'.$card->image" alt="">
    <div class="absolute top-0 left-0 right-0 p-1">
        <div class="grid grid-cols-2">
            <div class="flex justify-start">
                <div class="card-stat" :if="$card->type->isActive()">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"></path>
                    </svg>
                </div>
                <div class="card-stat" :if="$card->type->isPassive()">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l7.5-7.5 7.5 7.5m-15 6l7.5-7.5 7.5 7.5"></path>
                    </svg>
                </div>
            </div>

            <div class="flex justify-end">
                <div class="card-stat mana">{{ $card->mana }}</div>
            </div>
        </div>
    </div>

    <div class="card-content">
        <div class="card-name">{{ $card->name }}</div>
        <div class="card-price" :isset="$price">{{ $price }} coins</div>
        <div class="card-description gap-1">
            <span>{{ $card->description }}</span>
            <span :if="$includeLevel ?? false">Required level: {{ $card->level->getName() }}</span>
        </div>
    </div>
</div>
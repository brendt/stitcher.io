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
                <div class="card-stat card-stat-active" :if="$card->type->isActive()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="currentColor" d="M15.75 16v-2.8l-1.9-3.475q-.5.25-.8.725t-.3 1.05v8l1.425 2.5H22L21 9.5l-7-8l-.325.325q-.725.725-.862 1.7t.362 1.85L17.25 12.8V16zm-9 0v-3.2l4.075-7.425q.5-.875.338-1.85t-.863-1.7L10 1.5l-7 8L2 22h7.825l1.425-2.5v-8q0-.575-.312-1.05t-.788-.725L8.25 13.2V16z"></path>
                    </svg>
                </div>
                <div class="card-stat card-stat-passive" :if="$card->type->isPassive()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="currentColor" d="M20.5 22L7.4 9.5H1.9l5.8-7.775l3.3 1.65V6.65l3.675-.875l2.2 6.675L22 17.575L21.25 22zm-7.875 0L1.075 11.5h5.55L17.575 22z"></path>
                    </svg>
                </div>
            </div>

            <div class="flex justify-end" :if="$card->mana">
                <div class="card-stat mana">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M23 18v2h-2v1h-1v2h-2v-2h-1v-1h-2v-2h2v-1h1v-2h2v2h1v1zm0-14v2h-2v1h-1v2h-2V7h-1V6h-2V4h2V3h1V1h2v2h1v1zm-6 7v2h-2v1h-2v1h-1v1h-1v2h-1v2H8v-2H7v-2H6v-1H5v-1H3v-1H1v-2h2v-1h2V9h1V8h1V6h1V4h2v2h1v2h1v1h1v1h2v1z"/></svg>
                    {{ $card->mana }}
                </div>
            </div>
        </div>
    </div>

    <div class="card-content">
        <div class="card-name">{{ $card->name }}</div>
        <div class="card-price" :isset="$price">{{ number_format($price) }} coins</div>
        <div class="card-description gap-1">
            <span>{{ $card->description }}</span>
            <span :if="$includeLevel ?? false">Required level: {{ $card->level->getName() }}</span>
        </div>
    </div>
</div>
<?php

use App\Dungeon\Http\DungeonHomeController;
use function Tempest\Router\uri;

?>

<x-dungeon>
    <div class="flex flex-col items-center min-h-screen gap-6 p-6 pt-16 pb-24">
        <div class="w-full max-w-2xl flex flex-col gap-6">

            <div class="flex items-center justify-between">
                <h1 class="title text-3xl text-gray-200 tracking-wide">How to Play</h1>
                <a :href="uri([DungeonHomeController::class, 'index'])" class="text-sm text-gray-400 hover:text-amber-400 transition-colors">
                    ← Back
                </a>
            </div>

            {{-- Overview --}}
            <div class="bg-gray-950/90 backdrop-blur-md border border-white/10 rounded-2xl shadow-2xl p-6 flex flex-col gap-3">
                <h2 class="title text-lg text-amber-300 tracking-wide">Welcome to the Dungeon!</h2>
                <p class="text-gray-300 text-sm leading-relaxed">
                    The Dungeon is a daily card-driven exploration game. Enter the Dungeon, collect treasure, and survive as long as you can. Your <span class="text-amber-300 font-semibold">Victory Points</span> are your score — the higher, the better on the <a class="underline hover:no-underline font-semibold" :href="uri([DungeonHomeController::class, 'leaderboard'])">leaderboard</a>.
                </p>
            </div>

            {{-- Tokens & Runs --}}
            <div class="bg-gray-950/90 backdrop-blur-md border border-white/10 rounded-2xl shadow-2xl p-6 flex flex-col gap-3">
                <h2 class="title text-lg text-amber-300 tracking-wide">Tokens & Runs</h2>
                <p class="text-gray-300 text-sm leading-relaxed">
                    Each run costs <span class="text-white font-semibold">1 token</span>. Tokens replenish daily, so you get a fresh chance every day. Once inside, you move through the Dungeon tile by tile using the arrow keys or swiping on mobile. Your goal is to collect as many coins as possible and exit the Dungeon safely. If you can't get out, you lose everything you gathered.
                </p>
                <div class="flex gap-2 justify-center">
                    <img src="/dungeon/tutorial/exit.png" class="rounded-2xl max-w-[85%]" alt="exit">
                </div>
            </div>

            {{-- Health, Mana, and Stability --}}
            <div class="bg-gray-950/90 backdrop-blur-md border border-white/10 rounded-2xl shadow-2xl p-6 flex flex-col gap-3">
                <h2 class="title text-lg text-amber-300 tracking-wide">Health, Mana, and Stability</h2>
                <div class="flex flex-col gap-2 text-sm text-gray-300 leading-relaxed">
                    <p>
                        Surviving the Dungeon for as long as possible is a game of carefully balancing your three main stats: <span class="text-green-400 font-semibold">Health</span>, <span class="text-cyan-400 font-semibold">Mana</span>, and <span class="text-orange-400 font-semibold">Stability</span>.
                    </p>
                    <p>
                        <span class="text-green-400 font-semibold">Health</span> is what keeps you alive. If it reaches zero, your run ends immediately and you lose everything you gathered in that run.
                    </p>
                    <p>
                        <span class="text-cyan-400 font-semibold">Mana</span> is the energy needed to play cards. Mana is gained simply by walking around the Dungeon, or in later stages of the game by playing special cards.
                    </p>
                    <p>
                        <span class="text-orange-400 font-semibold">Stability</span> represents how stable the Dungeon is around you. As it drops, tiles begin to collapse — shrinking the playable area.
                    </p>
                </div>
            </div>

            {{-- Mana & Cards --}}
            <div class="bg-gray-950/90 backdrop-blur-md border border-white/10 rounded-2xl shadow-2xl p-6 flex flex-col gap-3">
                <h2 class="title text-lg text-amber-300 tracking-wide">Cards</h2>
                <p class="text-gray-300 text-sm leading-relaxed">
                    Cards are your primary tools for navigating the Dungeon. Each card has a <span class="text-cyan-400 font-semibold">mana</span> cost listed in the top right corner. Some cards immediately have an effect while played, some require interaction, and others give a passive effect while walking around. The type of card is indicated by a symbol on the top right.
                </p>
                <p class="text-gray-300 text-sm leading-relaxed">
                    Cards come in three rarities: <span class="text-gray-300 font-semibold">Common</span>, <span class="text-blue-400 font-semibold">Rare</span>, and <span class="text-purple-300 font-semibold">Epic</span>. Higher-rarity cards have stronger effects. You can buy new cards from the shop after every successful Dungeon run
                </p>

                <div class="flex gap-2 justify-center">
                    <img src="/dungeon/tutorial/card-1.png" class="rounded-2xl max-w-[45%]" alt="card-1">
                    <img src="/dungeon/tutorial/card-2.png" class="rounded-2xl max-w-[45%]" alt="card-2">
                </div>

                <p class="text-gray-300 text-sm leading-relaxed">
                    Between runs you manage your deck. You can hold up to <span class="text-white font-semibold">20 cards</span> in your active deck. Buy new cards from the shop with coins, or sell cards you no longer need.
                    Cards you own but haven't added to your deck stay in your collection. Only your active deck is available during a run — so choose wisely.
                </p>
            </div>

            {{-- Resources --}}
            <div class="bg-gray-950/90 backdrop-blur-md border border-white/10 rounded-2xl shadow-2xl p-6 flex flex-col gap-3">
                <h2 class="title text-lg text-amber-300 tracking-wide">Resources</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div class="flex flex-col gap-1">
                        <span class="font-semibold text-amber-400 tracking-widest">Coins</span>
                        <span class="text-gray-300 leading-relaxed">Coins are found randomly in the Dungeon. You get the most coins by finding <span class="text-purple-400 font-semibold">artifacts</span>.</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="font-semibold text-purple-400 tracking-widest">Artifacts</span>
                        <span class="text-gray-300 leading-relaxed">Track down the purple dot in the Dungeon to find hidden treasure.</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="font-semibold text-yellow-300 tracking-widest">Victory Points</span>
                        <span class="text-gray-300 leading-relaxed">Your score. You gain victory points by completing a Dungeon run or sometimes buy them in the shop.</span>
                    </div>
<!--                    <div class="flex flex-col gap-1">-->
<!--                        <span class="font-semibold text-pink-400 tracking-widest">Shards</span>-->
<!--                        <span class="text-gray-300 leading-relaxed">A rarer currency found in the Dungeon. Used to access special items in the shard shop.</span>-->
<!--                    </div>-->
                </div>
                <div class="flex gap-2 justify-center">
                    <img src="/dungeon/tutorial/artifact.png" class="rounded-2xl max-w-[60%]" alt="exit">
                </div>
            </div>

            <div class="flex justify-center pt-2">
                <a :href="uri([DungeonHomeController::class, 'index'])" class="title bg-amber-800 border-2 border-amber-600 hover:bg-amber-700 hover:border-amber-500 px-8 py-3 rounded-xl shadow-lg shadow-amber-950/60 text-amber-100 hover:text-white transition-all text-base">
                    Start playing
                </a>
            </div>

        </div>
    </div>
</x-dungeon>

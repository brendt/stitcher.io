<?php

use App\Dungeon\Http\DungeonHomeController;
use function Tempest\Router\uri;

/** @var array $leaderboard */

?>

<x-dungeon>
    <div class="flex flex-col items-center min-h-screen gap-6 p-6 pt-16">
        <div class="w-full max-w-2xl flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <h1 class="title text-3xl text-gray-200 tracking-wide">Leaderboard</h1>
                <a :href="uri([DungeonHomeController::class, 'index'])" class="text-sm text-gray-400 hover:text-amber-400 transition-colors">
                    ← Back
                </a>
            </div>

            <div class="bg-gray-950/90 backdrop-blur-md border border-white/10 rounded-2xl shadow-2xl overflow-hidden">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-white/10 text-xs text-gray-500 uppercase tracking-widest">
                        <th class="text-left px-6 py-4 w-12">#</th>
                        <th class="text-left px-6 py-4">Player</th>
                        <th class="text-right px-6 py-4">Victory Points</th>
                        <th class="text-right px-6 py-4">Wins/Games</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr
                        :foreach="$leaderboard as $i => $stats"
                        class="border-b border-white/5 hover:bg-white/5 transition-colors <?= $i === 0 ? 'text-amber-300' : ($i === 1 ? 'text-gray-300' : ($i === 2 ? 'text-amber-700' : 'text-gray-400')) ?>">
                        <td class="px-6 py-4 flex gap-1">
                            <span :if="$i === 0" class="text-lg">🥇</span>
                            <span :elseif="$i === 1" class="text-lg">🥈</span>
                            <span :elseif="$i === 2" class="text-lg">🥉</span>
                            {{ $i + 1 }}
                        </td>
                        <td class="px-6 py-4 title">{{ $stats->nickname ?? 'Anonymous' }}</td>
                        <td class="px-6 py-4 text-right">{{ number_format($stats->victoryPoints) }}</td>
                        <td class="px-6 py-4 text-right">{{ number_format($stats->wins)}}&thinsp;/&thinsp;{{number_format($stats->games) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-dungeon>

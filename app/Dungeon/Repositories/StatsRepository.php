<?php

namespace App\Dungeon\Repositories;

use App\Dungeon\Dungeon;
use App\Dungeon\Persistence\DungeonUserStats;
use App\Support\Authentication\User;
use function Tempest\Database\query;

final class StatsRepository
{
    public function increaseStats(
        User $user,
        int $coins = 0,
        int $experience = 0,
        int $victoryPoints = 0,
        int $tokens = 0,
        int $shards = 0,
        int $wins = 0,
        int $games = 0,
        int $losses = 0,
    ): void
    {
        $stats = $this->forUser($user);

        $stats->coins += $coins;
        $stats->experience += $experience;
        $stats->victoryPoints += $victoryPoints;
        $stats->tokens += $tokens;
        $stats->shards += $shards;
        $stats->wins += $wins;
        $stats->games += $games;
        $stats->losses += $losses;

        $stats->save();
    }

    public function forUser(User $user): DungeonUserStats
    {
        $stats = query(DungeonUserStats::class)
            ->select()
            ->where('userId', $user->id->value)
            ->where('campaignId', Dungeon::CURRENT_CAMPAIGN)
            ->first();

        if (! $stats) {
            $stats = query(DungeonUserStats::class)
                ->create(
                    userId: $user->id->value,
                    campaignId: Dungeon::CURRENT_CAMPAIGN,
                    coins: 0,
                    tokens: 10,
                    victoryPoints: 0,
                    experience: 0,
                    wins: 0,
                    losses: 0,
                    games: 0,
                    shards: 0,
                    runPrice: 2500,
                    extra: [Dungeon::HAS_SEEN_SHARD_SHOP => false],
                );
        }

        return $stats;
    }

    public function getRank(User $user): int
    {
        $stats = $this->forUser($user);

        return query(DungeonUserStats::class)
                ->count()
                ->where('victoryPoints > ?', $stats->victoryPoints)
                ->where('campaignId', Dungeon::CURRENT_CAMPAIGN)
                ->execute() + 1;
    }

    /** @return array<DungeonUserStats> */
    public function getLeaderboard(): array
    {
        return query(DungeonUserStats::class)
            ->select()
            ->where('campaignId', Dungeon::CURRENT_CAMPAIGN)
            ->orderBy('victoryPoints DESC')
            ->limit(100)
            ->all();
    }

    public function decreaseCoins(User $user, int $amount): void
    {
        $stats = $this->forUser($user);

        $stats->coins = $stats->coins - $amount;

        $stats->save();
    }

    public function decreaseTokens(User $user, int $amount): void
    {
        $stats = $this->forUser($user);

        $stats->tokens = $stats->tokens - $amount;

        $stats->save();
    }
}
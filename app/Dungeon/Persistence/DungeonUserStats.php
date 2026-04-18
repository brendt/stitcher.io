<?php

namespace App\Dungeon\Persistence;

use App\Dungeon\Level;
use Tempest\Database\IsDatabaseModel;

final class DungeonUserStats
{
    use IsDatabaseModel;

    public int $userId;
    public int $campaignId;
    public int $coins;
    public int $tokens;
    public int $victoryPoints;
    public int $experience;
    public int $wins;
    public int $losses;
    public int $games;
    public int $shards;
    public int $runPrice;
    public int $avatarUrl;
    public array $extra;

    public Level $level {
        get => Level::forExperience($this->experience);
    }

    public function canBuy(DungeonShopCard $dungeonShopCard): bool
    {
        if (! $this->level->hasAccessTo($dungeonShopCard->card->level)) {
            return false;
        }

        if ($this->coins < $dungeonShopCard->card->price) {
            return false;
        }

        return true;
    }
}
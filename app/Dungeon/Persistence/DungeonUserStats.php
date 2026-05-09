<?php

namespace App\Dungeon\Persistence;

use App\Dungeon\Level;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Virtual;

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

    public ?string $nickname {
        get => $this->extra['nickname'] ?? null;
    }

    public string $formattedExperience {
        get {
            if ($this->experience < 1000) {
                return "$this->experience";
            }

            return number_format($this->experience / 1000, 1) . 'k';
        }
    }

    public function canBuy(DungeonShopCard $dungeonShopCard): bool
    {
        if (! $this->level->hasAccessTo($dungeonShopCard->card->level)) {
            return false;
        }

        if ($this->coins < $dungeonShopCard->price) {
            return false;
        }

        return true;
    }
}
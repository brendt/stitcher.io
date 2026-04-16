<?php

namespace App\Dungeon\Persistence;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;

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
}
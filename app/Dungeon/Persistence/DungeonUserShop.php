<?php

namespace App\Dungeon\Persistence;

use Tempest\Database\IsDatabaseModel;

final class DungeonUserShop
{
    use IsDatabaseModel;

    public int $userId;
    public int $campaignId;
    public string $card;
}
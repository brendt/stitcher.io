<?php

namespace App\Dungeon\Persistence;

use App\Dungeon\Card;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Virtual;

final class DungeonShopCard
{
    use IsDatabaseModel;

    public int $userId;
    public int $campaignId;
    public string $cardName;
    public int $price;

    #[Virtual]
    public ?Card $card = null;
}
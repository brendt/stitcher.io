<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;

// TODO
final class EmergencyExitMinor implements Card
{
    use IsCard;

    private(set) string $name = "Emergency Exit";

    private(set) string $description = "Exit the dungeon with 20% of collected coins.";

    private(set) int $mana = 150;

    private(set) Rarity $rarity = Rarity::RARE;

    private(set) string $image = "/cards/emergency-minor.png";

    private(set) int $price = 5000;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::MASTER;

    public function play(Dungeon $dungeon): void
    {
        // $board->coins = $board->coins * 0.2;
        // $board->exitDungeon();
    }
}

<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;

final class EmergencyExitMajor implements Card
{
    use IsCard;

    private(set) string $name = "Emergency Exit++";

    private(set) string $description = "Exit the dungeon with 70% of collected coins.";

    private(set) int $mana = 200;

    private(set) Rarity $rarity = Rarity::EPIC;

    private(set) string $image = "/cards/emergency-major.png";

    private(set) int $price = 10_000;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::GRANDMASTER;

    public function play(Dungeon $dungeon): void
    {
        // $board->coins = $board->coins * 0.7;
        // $board->exitDungeon();
    }
}

<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\InteractsWithTile;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Commands\RemoveTileWalls;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class BreakthroughMinor implements Card, InteractsWithTile
{
    use IsCard;

    private(set) string $name = 'Breakthrough';

    private(set) string $description = "Remove a wall, -20 stability";

    private(set) string $image = '/cards/breakthrough-minor.png';

    private(set) int $mana = 20;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 100;

    private(set) Type $type = Type::ACTIVE;

    private(set) Level $level = Level::NOOB;

    public function play(Dungeon $dungeon): void
    {
        // Nothing on play
    }

    public function canInteractWithTile(Dungeon $dungeon, Tile $tile): bool
    {
        return ! $tile->isCollapsed;
    }

    public function interactWithTile(Dungeon $dungeon, Tile $tile): void
    {
        $dungeon->removeTileWalls($tile);
        $dungeon->decreaseStability(20);
        $dungeon->unsetActiveCard();
    }
}

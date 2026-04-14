<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\InteractsWithTile;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Commands\DiscardActiveCard;
use App\Dungeon\Commands\RemoveTileCollapse;
use App\Dungeon\Commands\RemoveTileWalls;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class RumbleMinor implements Card, InteractsWithTile
{
    use IsCard;

    private(set) string $name = 'Rumble';

    private(set) string $description = "Clear 1 collapse, -10 stability";

    private(set) string $image = '/cards/rumble-minor.png';

    private(set) int $mana = 30;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 150;

    private(set) Type $type = Type::ACTIVE;

    private(set) Level $level = Level::NOOB;

    public function play(Dungeon $dungeon): void
    {
        // $board->setActiveCard($this);
    }

    public function canInteractWithTile(Dungeon $dungeon, Tile $tile): bool
    {
        return $tile->isCollapsed;
    }

    public function interactWithTile(Dungeon $dungeon, Tile $tile): void
    {
        command(new RemoveTileWalls($tile->point));
        command(new RemoveTileCollapse($tile->point));
        command(new ChangeStability(-10));
        command(new DiscardActiveCard());
    }
}

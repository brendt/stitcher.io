<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Cards\Support\CheckBeforePlaying;
use App\Dungeon\Cards\Support\InteractsWithTile;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Commands\DiscardActiveCard;
use App\Dungeon\Commands\DiscardPassiveCard;
use App\Dungeon\Commands\RemoveTileCollapse;
use App\Dungeon\Commands\RemoveTileWalls;
use App\Dungeon\Direction;
use App\Dungeon\Level;
use App\Dungeon\Tile;
use Illuminate\Support\Str;

final class RumbleMajor implements Card, InteractsWithTile
{
    use IsCard;

    private int $count = 3;

    private(set) string $name = 'Rumble++';

    private(set) string $description = "Clear 3 collapses, -10 stability/clear";

    private(set) string $image = '/cards/rumble-major.png';

    private(set) int $mana = 70;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 750;

    private(set) Type $type = Type::ACTIVE;

    private(set) Level $level = Level::NOVICE;

    public function play(Dungeon $dungeon): void
    {
        // $board->setActiveCard($this);
    }

    public function canInteractWithTile(Board $board, Tile $tile): bool
    {
        return $tile->isCollapsed;
    }

    public function interactWithTile(Board $board, Tile $tile): void
    {
        command(new RemoveTileWalls($tile->point));
        command(new RemoveTileCollapse($tile->point));
        command(new ChangeStability(-10));
        $this->count -= 1;

        if ($this->count === 0) {
            command(new DiscardActiveCard());
        }
    }
}

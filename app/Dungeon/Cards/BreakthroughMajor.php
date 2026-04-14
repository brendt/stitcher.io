<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Cards\Support\InteractsWithTile;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Commands\RemoveTileWalls;
use App\Dungeon\Level;
use App\Dungeon\Tile;
use Illuminate\Support\Str;

final class BreakthroughMajor implements Card, InteractsWithTile
{
    use IsCard;

    public int $count = 3;

    private(set) string $name = 'Breakthrough++';

    private(set) string $description = "Remove 3 walls, -10 stability/wall";

    private(set) string $image = '/cards/breakthrough-major.png';

    private(set) int $mana = 70;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 500;

    private(set) Type $type = Type::ACTIVE;

    private(set) Level $level = Level::NOVICE;

    public function play(Dungeon $dungeon): void
    {
        // $board->setActiveCard($this);
    }

    public function canInteractWithTile(Board $board, Tile $tile): bool
    {
        return ! $tile->isCollapsed;
    }

    public function interactWithTile(Board $board, Tile $tile): void
    {
        command(new RemoveTileWalls($tile->point));
        command(new ChangeStability(-10));
        $this->count -= 1;

        if ($this->count === 0) {
            $board->discardActiveCard();
        }
    }
}

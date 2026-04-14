<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\CheckBeforePlaying;
use App\Dungeon\Cards\Support\InteractsWithTile;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
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
    use CardTrait;

    private int $count = 3;

    public function getName(): string
    {
        return 'Rumble++';
    }

    public function getDescription(): string
    {
        $collapses = Str::plural('collapse', $this->count);

        return "Clear {$this->count} {$collapses}, -10 stability/clear";
    }

    public function play(Board $board): void
    {
        $board->setActiveCard($this);
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

    public function getImage(): string
    {
        return '/cards/rumble-major.png';
    }

    public function getMana(): int
    {
        return 70;
    }

    public function getRarity(): Rarity
    {
        return Rarity::COMMON;
    }

    public function getPrice(): int
    {
        return 750;
    }

    public function getType(): Type
    {
        return Type::ACTIVE;
    }

    public function getLevel(): Level
    {
        return Level::NOVICE;
    }
}

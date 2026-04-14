<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\InteractsWithTile;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Commands\RemoveTileWalls;
use App\Dungeon\Level;
use App\Dungeon\Tile;
use Illuminate\Support\Str;

final class BreakthroughMajor implements Card, InteractsWithTile
{
    use CardTrait;

    public int $count = 3;

    public function getName(): string
    {
        return 'Breakthrough++';
    }

    public function getDescription(): string
    {
        $walls = Str::plural('wall', $this->count);

        return "Remove {$this->count} {$walls}, -10 stability/wall";
    }

    public function play(Board $board): void
    {
        $board->setActiveCard($this);
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

    public function getImage(): string
    {
        return '/cards/breakthrough-major.png';
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
        return 500;
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

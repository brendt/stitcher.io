<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\InteractsWithTile;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\DiscardActiveCard;
use App\Dungeon\Commands\SupportTile;
use App\Dungeon\Level;
use App\Dungeon\Tile;
use Illuminate\Support\Str;

final class SupportMinor implements Card, InteractsWithTile
{
    use CardTrait;

    public int $count = 10;

    public function getName(): string
    {
        return 'Support';
    }

    public function getDescription(): string
    {
        $tiles = Str::plural('tile', $this->count);

        return "Support {$this->count} {$tiles}, preventing collapses";
    }

    public function play(Board $board): void
    {
        $board->setActiveCard($this);
    }

    public function canInteractWithTile(Board $board, Tile $tile): bool
    {
        return ! $tile->isCollapsed
            && !$tile->isSupported
            && !$tile->isOrigin
            && !$tile->isAltar()
            && !$tile->isTrapped;
    }

    public function interactWithTile(Board $board, Tile $tile): void
    {
        command(new SupportTile($tile->point));

        $this->count -= 1;

        if ($this->count === 0) {
            command(new DiscardActiveCard());
        }
    }

    public function getImage(): string
    {
        return '/cards/support-minor.png';
    }

    public function getMana(): int
    {
        return 50;
    }

    public function getRarity(): Rarity
    {
        return Rarity::COMMON;
    }

    public function getPrice(): int
    {
        return 1500;
    }

    public function getType(): Type
    {
        return Type::ACTIVE;
    }

    public function getLevel(): Level
    {
        return Level::NOOB;
    }
}

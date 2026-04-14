<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\CheckBeforePlaying;
use App\Dungeon\Cards\Support\InteractsWithTile;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\RemoveTileTrap;
use App\Dungeon\Level;
use App\Dungeon\Tile;

// TODO test
final readonly class TrapDisarmMinor implements Card, InteractsWithTile
{
    use CardTrait;

    public function getName(): string
    {
        return 'Trap Disarm';
    }

    public function getDescription(): string
    {
        return "Clear 1 trapped tile";
    }

    public function play(Board $board): void
    {
        $board->setActiveCard($this);
    }

    public function canInteractWithTile(Board $board, Tile $tile): bool
    {
        return $tile->isTrapped;
    }

    public function interactWithTile(Board $board, Tile $tile): void
    {
        command(new RemoveTileTrap($tile->point));

        $board->discardActiveCard();
    }

    public function getImage(): string
    {
        return '/cards/trap-disarm-minor.png';
    }

    public function getMana(): int
    {
        return 30;
    }

    public function getRarity(): Rarity
    {
        return Rarity::COMMON;
    }

    public function getPrice(): int
    {
        return 100;
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

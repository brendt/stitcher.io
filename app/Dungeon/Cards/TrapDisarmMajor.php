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
use App\Dungeon\Direction;
use App\Dungeon\Level;
use App\Dungeon\Tile;
use Illuminate\Support\Str;

// TODO test
final class TrapDisarmMajor implements Card, InteractsWithTile
{
    use CardTrait;

    private int $count = 3;

    public function getName(): string
    {
        return 'Trap Disarm++';
    }

    public function getDescription(): string
    {
        $traps = Str::plural('trap', $this->count);

        return "Clear {$this->count} {$traps}";
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

        $this->count -= 1;

        if ($this->count === 0) {
            $board->discardActiveCard();
        }
    }

    public function getImage(): string
    {
        return '/cards/trap-disarm-major.png';
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
        return Level::MASTER;
    }
}

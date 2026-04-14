<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\InteractsWithTile;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\DiscardActiveCard;
use App\Dungeon\Commands\RemoveDweller;
use App\Dungeon\Level;
use App\Dungeon\Tile;
use Illuminate\Support\Str;

// TODO test
final class KillDwellerMajor implements Card, InteractsWithTile
{
    use CardTrait;

    private int $count = 3;

    public function getName(): string
    {
        return 'Hack and Slash';
    }

    public function getDescription(): string
    {
        $dwellers = Str::plural('dwellers', $this->count);

        return "Kill {$this->count} {$dwellers}";
    }

    public function play(Board $board): void
    {
        $board->setActiveCard($this);
    }

    public function canInteractWithTile(Board $board, Tile $tile): bool
    {
        return $board->getDweller($tile->point) !== null;
    }

    public function interactWithTile(Board $board, Tile $tile): void
    {
        command(new RemoveDweller($tile->point));

        $this->count -= 1;

        if ($this->count === 0) {
            command(new DiscardActiveCard());
        }
    }

    public function getImage(): string
    {
        return '/cards/kill-dweller-major.png';
    }

    public function getMana(): int
    {
        return 120;
    }

    public function getRarity(): Rarity
    {
        return Rarity::RARE;
    }

    public function getPrice(): int
    {
        return 2500;
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

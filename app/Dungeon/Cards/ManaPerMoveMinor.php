<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\HandlesEvents;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\ChangeMana;
use App\Dungeon\Commands\DiscardPassiveCard;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class ManaPerMoveMinor implements Card, HandlesEvents
{
    use CardTrait;

    public int $moves = 10;

    public function getName(): string
    {
        return "Mana Steps";
    }

    public function getDescription(): string
    {
        return "+10 mana per move for the next {$this->moves} moves";
    }

    public function getMana(): int
    {
        return 10;
    }

    public function getRarity(): Rarity
    {
        return Rarity::RARE;
    }

    public function getType(): Type
    {
        return Type::PASSIVE;
    }

    public function getImage(): string
    {
        return '/cards/mana-per-move-minor.png';
    }

    public function getPrice(): int
    {
        return 2000;
    }

    public function play(Board $board): void
    {
        $board->setPassiveCard($this);
    }

    public function handle(Board $board, Tile $tile, object $event): void
    {
        if (! $event instanceof PlayerMoved) {
            return;
        }

        command(new ChangeMana(10));
        $this->moves -= 1;

        if ($this->moves <= 0) {
            command(new DiscardPassiveCard());
        }
    }

    public function getLevel(): Level
    {
        return Level::MASTER;
    }
}

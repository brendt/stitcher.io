<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\HandlesEvents;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\ChangeHealth;
use App\Dungeon\Commands\DiscardPassiveCard;
use App\Dungeon\Commands\HideDweller;
use App\Dungeon\Commands\ShowDweller;
use App\Dungeon\Events\DamageDealt;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class BeaconMinor implements Card, HandlesEvents
{
    use CardTrait;

    public int $count = 10;

    public function getName(): string
    {
        return 'Beacon';
    }

    public function getDescription(): string
    {
        return "Illuminate the darkness for {$this->count} moves";
    }

    public function play(Board $board): void
    {
        $board->setPassiveCard($this);

        foreach ($board->getAllDwellers() as $dweller) {
            command(new ShowDweller($dweller->point));
        }
    }

    public function handle(Board $board, Tile $tile, object $event): void
    {
        if (! $event instanceof PlayerMoved) {
            return;
        }

        $this->count -= 1;

        if ($this->count === 0) {
            foreach ($board->getAllDwellers() as $dweller) {
                command(new HideDweller($dweller->point));
            }

            command(new DiscardPassiveCard());
        }
    }

    public function getImage(): string
    {
        return '/cards/beacon-minor.png';
    }

    public function getMana(): int
    {
        return 75;
    }

    public function getRarity(): Rarity
    {
        return Rarity::RARE;
    }

    public function getPrice(): int
    {
        return 2000;
    }

    public function getType(): Type
    {
        return Type::PASSIVE;
    }

    public function getLevel(): Level
    {
        return Level::NOVICE;
    }
}

<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\HandlesEvents;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\ChangeHealth;
use App\Dungeon\Events\DamageDealt;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class ProtectionMajor implements Card, HandlesEvents
{
    use CardTrait;

    public int $toAbsorb = 100;

    public function getName(): string
    {
        return 'Protection++';
    }

    public function getDescription(): string
    {
        return "Absorbs {$this->toAbsorb} damage";
    }

    public function play(Board $board): void
    {
        $board->setPassiveCard($this);
    }

    public function handle(Board $board, Tile $tile, object $event): void
    {
        if (! $event instanceof DamageDealt) {
            return;
        }

        if ($this->toAbsorb > $event->damage) {
            command(new ChangeHealth($event->damage));
            $this->toAbsorb -= $event->damage;
        } else {
            $board->discardPassiveCard();
            $overflow = $event->damage - $this->toAbsorb;
            command(new ChangeHealth($event->damage - $overflow));
        }
    }

    public function getImage(): string
    {
        return '/cards/protection-major.png';
    }

    public function getMana(): int
    {
        return 100;
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
        return Type::PASSIVE;
    }

    public function getLevel(): Level
    {
        return Level::MASTER;
    }
}

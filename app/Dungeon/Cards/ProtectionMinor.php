<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\WithEvents;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeHealth;
use App\Dungeon\Events\DamageDealt;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class ProtectionMinor implements Card, WithEvents
{
    use IsCard;

    public int $toAbsorb = 50;

    private(set) string $name = 'Protection';

    private(set) string $description = "Absorbs 50 damage";

    private(set) string $image = '/cards/protection-minor.png';

    private(set) int $mana = 50;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 500;

    private(set) Type $type = Type::PASSIVE;

    private(set) Level $level = Level::NOVICE;

    public function play(Dungeon $dungeon): void
    {
        // $board->setPassiveCard($this);
    }

    public function handle(Dungeon $dungeon, Tile $tile, object $event): void
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
}

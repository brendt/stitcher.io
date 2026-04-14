<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Cards\Support\HandlesEvents;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeHealth;
use App\Dungeon\Events\DamageDealt;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class ProtectionMajor implements Card, HandlesEvents
{
    use IsCard;

    public int $toAbsorb = 100;

    private(set) string $name = 'Protection++';

    private(set) string $description = "Absorbs 100 damage";

    private(set) string $image = '/cards/protection-major.png';

    private(set) int $mana = 100;

    private(set) Rarity $rarity = Rarity::RARE;

    private(set) int $price = 2500;

    private(set) Type $type = Type::PASSIVE;

    private(set) Level $level = Level::MASTER;

    public function play(Dungeon $dungeon): void
    {
        // $board->setPassiveCard($this);
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
}

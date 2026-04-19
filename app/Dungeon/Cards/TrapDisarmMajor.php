<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Events\TileUpdated;
use App\Dungeon\ActiveCard;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;
use App\Dungeon\Tile;
use function Tempest\EventBus\event;

final class TrapDisarmMajor implements Card, ActiveCard
{
    use IsCard;

    private int $count = 3;

    private(set) string $name = 'Trap Disarm++';

    private(set) string $description = "Clear 3 traps";

    private(set) string $image = '/cards/trap-disarm-major.png';

    private(set) int $mana = 70;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 5000;

    private(set) Type $type = Type::ACTIVE;

    private(set) Level $level = Level::MASTER;

    public ?string $label {
        get => $this->count;
    }

    public function play(Dungeon $dungeon): void
    {
        // Nothing on play
    }

    public function canInteractWithTile(Dungeon $dungeon, Tile $tile): bool
    {
        return $tile->isTrapped;
    }

    public function interactWithTile(Dungeon $dungeon, Tile $tile): void
    {
        $tile->isTrapped = false;

        event(new TileUpdated($tile));

        $this->count -= 1;

        $dungeon->updateCard($this);

        if ($this->count === 0) {
            $dungeon->unsetActiveCard();
        }
    }
}

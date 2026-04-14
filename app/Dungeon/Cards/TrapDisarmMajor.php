<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\InteractsWithTile;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\RemoveTileTrap;
use App\Dungeon\Level;
use App\Dungeon\Tile;

// TODO test
final class TrapDisarmMajor implements Card, InteractsWithTile
{
    use IsCard;

    private int $count = 3;

    private(set) string $name = 'Trap Disarm++';

    private(set) string $description = "Clear 3 traps";

    private(set) string $image = '/cards/trap-disarm-major.png';

    private(set) int $mana = 70;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 500;

    private(set) Type $type = Type::ACTIVE;

    private(set) Level $level = Level::MASTER;

    public function play(Dungeon $dungeon): void
    {
        // $board->setActiveCard($this);
    }

    public function canInteractWithTile(Dungeon $dungeon, Tile $tile): bool
    {
        return $tile->isTrapped;
    }

    public function interactWithTile(Dungeon $dungeon, Tile $tile): void
    {
        command(new RemoveTileTrap($tile->point));

        $this->count -= 1;

        if ($this->count === 0) {
            $board->discardActiveCard();
        }
    }
}

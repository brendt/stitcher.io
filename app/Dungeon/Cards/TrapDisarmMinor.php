<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Cards\Support\CheckBeforePlaying;
use App\Dungeon\Cards\Support\InteractsWithTile;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\RemoveTileTrap;
use App\Dungeon\Level;
use App\Dungeon\Tile;

// TODO test
final class TrapDisarmMinor implements Card, InteractsWithTile
{
    use IsCard;

    private(set) string $name = 'Trap Disarm';

    private(set) string $description = "Clear 1 trapped tile";

    private(set) string $image = '/cards/trap-disarm-minor.png';

    private(set) int $mana = 30;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 100;

    private(set) Type $type = Type::ACTIVE;

    private(set) Level $level = Level::NOVICE;

    public function play(Dungeon $dungeon): void
    {
        // $board->setActiveCard($this);
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
}

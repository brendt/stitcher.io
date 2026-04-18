<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Events\TileUpdated;
use App\Dungeon\InteractsWithTile;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;
use App\Dungeon\Tile;
use function Tempest\EventBus\event;

final class TrapDisarmMinor implements Card, InteractsWithTile
{
    use IsCard;

    private(set) string $name = 'Trap Disarm';

    private(set) string $description = "Clear 1 trapped tile";

    private(set) string $image = '/cards/trap-disarm-minor.png';

    private(set) int $mana = 30;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 2000;

    private(set) Type $type = Type::ACTIVE;

    private(set) Level $level = Level::NOVICE;

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

        $dungeon->unsetActiveCard();
    }
}

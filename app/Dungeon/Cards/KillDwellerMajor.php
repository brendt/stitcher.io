<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\InteractsWithTile;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class KillDwellerMajor implements Card, InteractsWithTile
{
    use IsCard;

    private int $count = 3;

    private(set) string $name = 'Hack and Slash';

    private(set) string $description = "Kill 3 dwellers";

    private(set) string $image = '/cards/kill-dweller-major.png';

    private(set) int $mana = 120;

    private(set) Rarity $rarity = Rarity::RARE;

    private(set) int $price = 2500;

    private(set) Type $type = Type::ACTIVE;

    private(set) Level $level = Level::MASTER;

    public function play(Dungeon $dungeon): void
    {
        // Nothing on play
    }

    public function canInteractWithTile(Dungeon $dungeon, Tile $tile): bool
    {
        return $dungeon->getDweller($tile->point) !== null;
    }

    public function interactWithTile(Dungeon $dungeon, Tile $tile): void
    {
        $dungeon->despawnDweller($dungeon->getDweller($tile->point));

        $this->count -= 1;

        if ($this->count === 0) {
            $dungeon->unsetActiveCard();
        }
    }
}

<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\ActiveCard;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class KillDwellerMinor implements Card, ActiveCard
{
    use IsCard;

    private(set) string $name = 'Slice and Dice';

    private(set) string $description = "Kill 1 Dweller";

    private(set) string $image = '/cards/kill-dweller-minor.png';

    private(set) int $mana = 75;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 1000;

    private(set) Type $type = Type::ACTIVE;

    private(set) Level $level = Level::NOVICE;

    public ?string $label {
        get => null;
    }

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
        $dweller = $dungeon->getDweller($tile->point);

        $dungeon->despawnDweller($dweller);

        $dungeon->updateCard($this);

        $dungeon->unsetActiveCard();
    }
}

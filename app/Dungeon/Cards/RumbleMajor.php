<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\ActiveCard;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class RumbleMajor implements Card, ActiveCard
{
    use IsCard;

    private int $count = 3;

    private(set) string $name = 'Rumble++';

    private(set) string $description = "Clear 3 collapses, -10 stability/clear";

    private(set) string $image = '/cards/rumble-major.png';

    private(set) int $mana = 70;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 1250;

    private(set) Type $type = Type::ACTIVE;

    private(set) Level $level = Level::NOVICE;

    public ?string $label {
        get => $this->count;
    }

    public function play(Dungeon $dungeon): void
    {
        // Nothing on play
    }

    public function canInteractWithTile(Dungeon $dungeon, Tile $tile): bool
    {
        return $tile->isCollapsed;
    }

    public function interactWithTile(Dungeon $dungeon, Tile $tile): void
    {
        $dungeon->removeTileWalls($tile);
        $dungeon->removeTileCollapse($tile);
        $dungeon->decreaseStability(10);

        $this->count -= 1;

        $dungeon->updateCard($this);

        if ($this->count === 0) {
            $dungeon->unsetActiveCard();
        }
    }
}

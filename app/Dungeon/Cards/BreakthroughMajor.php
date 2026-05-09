<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\ActiveCard;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class BreakthroughMajor implements Card, ActiveCard
{
    use IsCard;

    public int $count = 3;

    private(set) string $name = 'Breakthrough++';

    private(set) string $description = "Remove 3 walls, -10 stability/wall";

    private(set) string $image = '/cards/breakthrough-major.png';

    private(set) int $mana = 70;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 2500;

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
        return ! $tile->isCollapsed;
    }

    public function interactWithTile(Dungeon $dungeon, Tile $tile): void
    {
        $dungeon->removeTileWalls($tile);
        $dungeon->decreaseStability(10);

        $this->count -= 1;

        $dungeon->updateCard($this);

        if ($this->count === 0) {
            $dungeon->unsetActiveCard();
        }
    }
}

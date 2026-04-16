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
use Illuminate\Support\Str;
use function Tempest\EventBus\event;

final class SupportMajor implements Card, InteractsWithTile
{
    use IsCard;

    public int $count = 25;

    private(set) string $name = 'Support++';

    private(set) string $description = "Support 25 tiles, preventing collapses";

    private(set) string $image = '/cards/support-major.png';

    private(set) int $mana = 75;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 1000;

    private(set) Type $type = Type::ACTIVE;

    private(set) Level $level = Level::NOVICE;

    public function play(Dungeon $dungeon): void
    {
        // Nothing on play
    }

    public function canInteractWithTile(Dungeon $dungeon, Tile $tile): bool
    {
        return ! $tile->isCollapsed
            && !$tile->isSupported
            && !$tile->isOrigin
            && !$tile->isAltar()
            && !$tile->isTrapped;
    }

    public function interactWithTile(Dungeon $dungeon, Tile $tile): void
    {
        $tile->isSupported = true;

        event(new TileUpdated($tile));

        $this->count -= 1;

        if ($this->count === 0) {
            $dungeon->unsetActiveCard();
        }
    }
}

<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Cards\Support\InteractsWithTile;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\DiscardActiveCard;
use App\Dungeon\Commands\SupportTile;
use App\Dungeon\Level;
use App\Dungeon\Tile;
use Illuminate\Support\Str;

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
        // $board->setActiveCard($this);
    }

    public function canInteractWithTile(Board $board, Tile $tile): bool
    {
        return ! $tile->isCollapsed
            && !$tile->isSupported
            && !$tile->isOrigin
            && !$tile->isAltar()
            && !$tile->isTrapped;
    }

    public function interactWithTile(Board $board, Tile $tile): void
    {
        command(new SupportTile($tile->point));

        $this->count -= 1;

        if ($this->count === 0) {
            command(new DiscardActiveCard());
        }
    }
}

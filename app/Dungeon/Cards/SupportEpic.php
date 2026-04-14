<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Cards\Support\CanBuyWithShards;
use App\Dungeon\Card;
use App\Dungeon\Cards\Support\InteractsWithTile;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Commands\DiscardActiveCard;
use App\Dungeon\Commands\SupportTile;
use App\Dungeon\Level;
use App\Dungeon\Tile;
use Illuminate\Support\Str;

final class SupportEpic implements Card, InteractsWithTile, CanBuyWithShards
{
    use IsCard;

    public int $count = 30;

    private(set) string $name = 'Support+++';

    private(set) string $description = "Support 30 tiles, +5 stability per tile";

    private(set) string $image = '/cards/support-epic.png';

    private(set) int $mana = 120;

    private(set) Rarity $rarity = Rarity::EPIC;

    private(set) int $price = 7000;

    private(set) Type $type = Type::ACTIVE;

    private(set) Level $level = Level::GRANDMASTER;

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
        command(new ChangeStability(5));
        $this->count -= 1;

        if ($this->count === 0) {
            command(new DiscardActiveCard());
        }
    }

    public function getAdjustedPrice(): int
    {
        return round($this->price / 3);
    }

    public function getShardPrice(): int
    {
        return 10;
    }
}

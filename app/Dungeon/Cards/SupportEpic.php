<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\CanBuyWithShards;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\InteractsWithTile;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Commands\DiscardActiveCard;
use App\Dungeon\Commands\SupportTile;
use App\Dungeon\Level;
use App\Dungeon\Tile;
use Illuminate\Support\Str;

final class SupportEpic implements Card, InteractsWithTile, CanBuyWithShards
{
    use CardTrait;

    public int $count = 30;

    public function getName(): string
    {
        return 'Support+++';
    }

    public function getDescription(): string
    {
        $tiles = Str::plural('tile', $this->count);

        return "Support {$this->count} {$tiles}, +5 stability per tile";
    }

    public function play(Board $board): void
    {
        $board->setActiveCard($this);
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

    public function getImage(): string
    {
        return '/cards/support-epic.png';
    }

    public function getMana(): int
    {
        return 120;
    }

    public function getRarity(): Rarity
    {
        return Rarity::EPIC;
    }

    public function getPrice(): int
    {
        return 7000;
    }

    public function getType(): Type
    {
        return Type::ACTIVE;
    }

    public function getLevel(): Level
    {
        return Level::GRANDMASTER;
    }

    public function getAdjustedPrice(): int
    {
        return round($this->getPrice() / 3);
    }

    public function getShardPrice(): int
    {
        return 10;
    }
}

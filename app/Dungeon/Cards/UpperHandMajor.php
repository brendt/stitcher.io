<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\CheckBeforePlaying;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\RemoveDweller;
use App\Dungeon\Commands\SpawnDweller;
use App\Dungeon\Level;

// TODO test
final readonly class UpperHandMajor implements Card, CheckBeforePlaying
{
    use CardTrait;

    public function getName(): string
    {
        return 'Upper Hands';
    }

    public function getDescription(): string
    {
        return 'Scare away all dwellers in sight';
    }

    public function getMana(): int
    {
        return 100;
    }

    public function getRarity(): Rarity
    {
        return Rarity::RARE;
    }

    public function getImage(): string
    {
        return '/cards/upperhand-major.png';
    }

    public function play(Board $board): void
    {
        foreach ($board->getVisibleDwellers() as $dweller) {
            command(new RemoveDweller($dweller->point));
            command(new SpawnDweller());
        }
    }

    public function canPlay(Board $board): bool
    {
        return iterator_count($board->getVisibleDwellers()) > 0;
    }

    public function getPrice(): int
    {
        return 2500;
    }

    public function getType(): Type
    {
        return Type::IMMEDIATE;
    }

    public function getLevel(): Level
    {
        return Level::NOVICE;
    }
}

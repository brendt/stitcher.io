<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Cards\Support\InteractsWithTile;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\DiscardActiveCard;
use App\Dungeon\Commands\RemoveDweller;
use App\Dungeon\Level;
use App\Dungeon\Tile;
use Illuminate\Support\Str;

// TODO test
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
        // $board->setActiveCard($this);
    }

    public function canInteractWithTile(Board $board, Tile $tile): bool
    {
        return $board->getDweller($tile->point) !== null;
    }

    public function interactWithTile(Board $board, Tile $tile): void
    {
        command(new RemoveDweller($tile->point));

        $this->count -= 1;

        if ($this->count === 0) {
            command(new DiscardActiveCard());
        }
    }
}

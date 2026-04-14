<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Cards\Support\HandlesEvents;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeMana;
use App\Dungeon\Commands\DiscardPassiveCard;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class ManaPerMoveMinor implements Card, HandlesEvents
{
    use IsCard;

    public int $moves = 10;

    private(set) string $name = "Mana Steps";

    private(set) string $description = "+10 mana per move for the next 10 moves";

    private(set) int $mana = 10;

    private(set) Rarity $rarity = Rarity::RARE;

    private(set) Type $type = Type::PASSIVE;

    private(set) string $image = '/cards/mana-per-move-minor.png';

    private(set) int $price = 2000;

    private(set) Level $level = Level::MASTER;

    public function play(Dungeon $dungeon): void
    {
        // $board->setPassiveCard($this);
    }

    public function handle(Board $board, Tile $tile, object $event): void
    {
        if (! $event instanceof PlayerMoved) {
            return;
        }

        command(new ChangeMana(10));
        $this->moves -= 1;

        if ($this->moves <= 0) {
            command(new DiscardPassiveCard());
        }
    }
}

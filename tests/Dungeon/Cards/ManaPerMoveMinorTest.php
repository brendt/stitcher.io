<?php

namespace Tests\Dungeon\Cards;

use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\ManaPerMoveMinor;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\PassiveCardUnset;
use App\Dungeon\Events\PlayerManaIncreased;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Point;
use PHPUnit\Framework\Attributes\Test;

final class ManaPerMoveMinorTest extends DungeonTest
{
    #[Test]
    public function handle_grants_10_mana_per_move(): void
    {
        $card = new ManaPerMoveMinor();
        $this->dungeon->mana = 0;

        $card->handle($this->dungeon, new PlayerMoved(from: new Point(0, 0), to: new Point(1, 0)));

        $this->assertSame(10, $this->dungeon->mana);
        $this->eventBus->assertDispatched(PlayerManaIncreased::class, function (PlayerManaIncreased $event) {
            $this->assertSame(10, $event->amount);
        });
    }

    #[Test]
    public function handle_decrements_move_count(): void
    {
        $card = new ManaPerMoveMinor(); // moves = 10

        $card->handle($this->dungeon, new PlayerMoved(from: new Point(0, 0), to: new Point(1, 0)));

        $this->assertSame(9, $card->moves);
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    #[Test]
    public function handle_unsets_passive_card_when_moves_reach_zero(): void
    {
        $card = new ManaPerMoveMinor();
        $card->moves = 1;
        $this->dungeon->setPassiveCard($card);

        $card->handle($this->dungeon, new PlayerMoved(from: new Point(0, 0), to: new Point(1, 0)));

        $this->assertSame(0, $card->moves);
        $this->assertNull($this->dungeon->passiveCard);
        $this->eventBus->assertDispatched(PassiveCardUnset::class);
    }

    #[Test]
    public function handle_ignores_unrelated_events(): void
    {
        $card = new ManaPerMoveMinor();

        $card->handle($this->dungeon, new \App\Dungeon\Events\TileGenerated(
            new \App\Dungeon\Tile(new Point(1, 0))
        ));

        $this->assertSame(10, $card->moves);
        $this->eventBus->assertNotDispatched(CardUpdated::class);
    }
}

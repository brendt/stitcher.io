<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\ManaPerMoveMinor;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\PassiveCardUnset;
use App\Dungeon\Events\PlayerManaIncreased;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Events\TileGenerated;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class ManaPerMoveMinorTest extends DungeonTest
{
    public function handle_grants_10_mana_per_move(): void
    {
        $card = new ManaPerMoveMinor();
        $this->dungeon->mana = 0;

        $card->handle($this->dungeon, new PlayerMoved(from: new Point(0, 0), to: new Point(1, 0)));

        Assert::same($this->dungeon->mana, 10);
        $this->eventBus->assertDispatched(PlayerManaIncreased::class, function (PlayerManaIncreased $event) {
            Assert::same($event->amount, 10);
        });
    }

    public function handle_decrements_move_count(): void
    {
        $card = new ManaPerMoveMinor(); // moves = 10

        $card->handle($this->dungeon, new PlayerMoved(from: new Point(0, 0), to: new Point(1, 0)));

        Assert::same($card->moves, 9);
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    public function handle_unsets_passive_card_when_moves_reach_zero(): void
    {
        $card = new ManaPerMoveMinor();
        $card->moves = 1;
        $this->dungeon->setPassiveCard($card);

        $card->handle($this->dungeon, new PlayerMoved(from: new Point(0, 0), to: new Point(1, 0)));

        Assert::same($card->moves, 0);
        Assert::null($this->dungeon->passiveCard);
        $this->eventBus->assertDispatched(PassiveCardUnset::class);
    }

    public function handle_ignores_unrelated_events(): void
    {
        $card = new ManaPerMoveMinor();

        $card->handle(
            $this->dungeon,
            new TileGenerated(
                new Tile(new Point(1, 0)),
            ),
        );

        Assert::same($card->moves, 10);
        $this->eventBus->assertNotDispatched(CardUpdated::class);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\ManaPerMovePermanent;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\PlayerManaIncreased;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Events\TileGenerated;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class ManaPerMovePermanentTest extends DungeonTest
{
    public function handle_grants_1_mana_per_move(): void
    {
        $card = new ManaPerMovePermanent();
        $this->dungeon->mana = 0;

        $card->handle($this->dungeon, new PlayerMoved(from: new Point(0, 0), to: new Point(1, 0)));

        Assert::same($this->dungeon->mana, 1);
        $this->eventBus->assertDispatched(PlayerManaIncreased::class, function (PlayerManaIncreased $event) {
            Assert::same($event->amount, 1);
        });
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    public function handle_ignores_unrelated_events(): void
    {
        $card = new ManaPerMovePermanent();
        $this->dungeon->mana = 0;

        $card->handle(
            $this->dungeon,
            new TileGenerated(
                new Tile(new Point(1, 0)),
            ),
        );

        Assert::same($this->dungeon->mana, 0);
        $this->eventBus->assertNotDispatched(CardUpdated::class);
    }
}

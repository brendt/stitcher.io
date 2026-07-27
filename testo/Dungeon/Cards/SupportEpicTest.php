<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use Testo\Core\Exception\SkipTest;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class SupportEpicTest extends DungeonTest
{
    public function interact_with_tile(): void
    {
        // TODO: SupportEpic.interactWithTile() is not yet implemented
        throw new SkipTest('SupportEpic is not yet implemented');
    }
}

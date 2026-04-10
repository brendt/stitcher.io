<?php

declare(strict_types=1);

namespace Tests\Game;

use App\Game\Domain\ClaimRule;
use App\Game\Domain\Station;
use DomainException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ClaimRuleTest extends TestCase
{
    #[Test]
    public function reclaim_requires_plus_one_over_current_top_value(): void
    {
        $rule = new ClaimRule(cap: 5);
        $ownedStation = new Station(id: 'A', ownerId: 'player-a', topValue: 5);

        $this->expectException(DomainException::class);
        $rule->apply(station: $ownedStation, playerId: 'player-b', deposit: 5);
    }

    #[Test]
    public function reclaim_with_plus_one_is_allowed(): void
    {
        $rule = new ClaimRule(cap: 5);
        $ownedStation = new Station(id: 'A', ownerId: 'player-a', topValue: 5);

        $claimed = $rule->apply(station: $ownedStation, playerId: 'player-b', deposit: 6);

        self::assertSame('player-b', $claimed->ownerId);
        self::assertSame(6, $claimed->topValue);
    }

    #[Test]
    public function deposit_cannot_exceed_top_value_plus_cap(): void
    {
        $rule = new ClaimRule(cap: 5);
        $ownedStation = new Station(id: 'A', ownerId: 'player-a', topValue: 5);

        $this->expectException(DomainException::class);
        $rule->apply(station: $ownedStation, playerId: 'player-b', deposit: 11);
    }

    #[Test]
    public function deposit_equal_to_top_value_plus_cap_is_allowed(): void
    {
        $rule = new ClaimRule(cap: 5);
        $ownedStation = new Station(id: 'A', ownerId: 'player-a', topValue: 5);

        $claimed = $rule->apply(station: $ownedStation, playerId: 'player-b', deposit: 10);

        self::assertSame('player-b', $claimed->ownerId);
        self::assertSame(10, $claimed->topValue);
    }
}

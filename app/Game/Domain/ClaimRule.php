<?php

declare(strict_types=1);

namespace App\Game\Domain;

use DomainException;
use InvalidArgumentException;

final class ClaimRule
{
    public function __construct(public readonly int $cap = 5)
    {
        if ($this->cap < 1) {
            throw new InvalidArgumentException('Claim cap must be at least 1.');
        }
    }

    public function apply(Station $station, string $playerId, int $deposit): Station
    {
        if ($deposit < 1) {
            throw new DomainException('Deposit must be at least 1 coin.');
        }

        if ($station->isOwnedBy($playerId)) {
            throw new DomainException('Owner cannot add coins to their own station.');
        }

        if ($station->isNeutral()) {
            return $station->withClaim(ownerId: $playerId, topValue: $deposit);
        }

        $minimumDeposit = $station->topValue + 1;
        $maximumDeposit = $station->topValue + $this->cap;

        if ($deposit < $minimumDeposit) {
            throw new DomainException(sprintf('Deposit must be at least %d.', $minimumDeposit));
        }

        if ($deposit > $maximumDeposit) {
            throw new DomainException(sprintf('Deposit cannot exceed %d.', $maximumDeposit));
        }

        return $station->withClaim(ownerId: $playerId, topValue: $deposit);
    }
}

<?php

declare(strict_types=1);

namespace App\Game\Domain;

use InvalidArgumentException;

final class Station
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $ownerId = null,
        public readonly int $topValue = 0,
        public readonly bool $isHub = false,
    ) {
        if ($this->topValue < 0) {
            throw new InvalidArgumentException('Station top value cannot be negative.');
        }

        if ($this->ownerId === null && $this->topValue !== 0) {
            throw new InvalidArgumentException('Neutral station top value must be zero.');
        }
    }

    public function isNeutral(): bool
    {
        return $this->ownerId === null;
    }

    public function isOwnedBy(string $playerId): bool
    {
        return $this->ownerId === $playerId;
    }

    public function withClaim(string $ownerId, int $topValue): self
    {
        return new self(
            id: $this->id,
            ownerId: $ownerId,
            topValue: $topValue,
            isHub: $this->isHub,
        );
    }
}

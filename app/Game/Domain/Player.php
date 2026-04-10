<?php

declare(strict_types=1);

namespace App\Game\Domain;

use InvalidArgumentException;

final class Player
{
    public function __construct(
        public readonly string $id,
        public readonly int $coins,
        public readonly ?string $stationId = null,
    ) {
        if ($this->coins < 0) {
            throw new InvalidArgumentException('Player coins cannot be negative.');
        }
    }

    public function canAfford(int $coins): bool
    {
        return $this->coins >= $coins;
    }

    public function spend(int $coins): self
    {
        if ($coins < 0) {
            throw new InvalidArgumentException('Spend amount cannot be negative.');
        }

        if (! $this->canAfford($coins)) {
            throw new InvalidArgumentException('Player cannot afford this amount.');
        }

        return new self(
            id: $this->id,
            coins: $this->coins - $coins,
            stationId: $this->stationId,
        );
    }

    public function earn(int $coins): self
    {
        if ($coins < 0) {
            throw new InvalidArgumentException('Earn amount cannot be negative.');
        }

        return new self(
            id: $this->id,
            coins: $this->coins + $coins,
            stationId: $this->stationId,
        );
    }

    public function atStation(string $stationId): self
    {
        return new self(
            id: $this->id,
            coins: $this->coins,
            stationId: $stationId,
        );
    }
}

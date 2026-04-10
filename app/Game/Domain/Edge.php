<?php

declare(strict_types=1);

namespace App\Game\Domain;

use InvalidArgumentException;

final class Edge
{
    public function __construct(
        public readonly string $fromStationId,
        public readonly string $toStationId,
        public readonly int $travelTimeSeconds,
        public readonly bool $isExpress = false,
    ) {
        if ($this->travelTimeSeconds <= 0) {
            throw new InvalidArgumentException('Travel time must be greater than zero.');
        }
    }
}

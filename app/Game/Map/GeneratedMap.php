<?php

declare(strict_types=1);

namespace App\Game\Map;

use App\Game\Domain\Edge;
use App\Game\Domain\Station;

final class GeneratedMap
{
    /**
     * @param array<string, Station> $stations
     * @param list<Edge> $edges
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     */
    public function __construct(
        public readonly array $stations,
        public readonly array $edges,
        public readonly array $stationCoordinates,
    ) {}
}

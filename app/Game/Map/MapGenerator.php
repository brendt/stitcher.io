<?php

declare(strict_types=1);

namespace App\Game\Map;

use App\Game\Domain\Edge;
use App\Game\Domain\Station;
use InvalidArgumentException;
use Random\Engine\Mt19937;
use Random\Randomizer;

final readonly class MapGenerator
{
    public function __construct(
        private float $hubRatio = 0.05,
    ) {}

    public function generate(int $stationCount, int $seed, int $playerCount = 2): GeneratedMap
    {
        if ($stationCount < 8) {
            throw new InvalidArgumentException('Station count must be at least 8.');
        }

        $gridSize = $playerCount <= 2 ? 100 : 120;

        $random = new Randomizer(new Mt19937($seed));

        $startXMin = (int) floor($gridSize * 0.15);
        $startXMax = (int) ceil($gridSize * 0.85);
        $startYMin = (int) floor($gridSize * 0.08);
        $startYMax = (int) ceil($gridSize * 0.20);

        $x = $random->getInt($startXMin, $startXMax);
        $y = $random->getInt($startYMin, $startYMax);

        $stationCoordinates = [];
        $stationIds = [];

        $stationCounter = 1;
        $firstId = sprintf('S%d', $stationCounter++);
        $stationIds[] = $firstId;
        $stationCoordinates[$firstId] = ['x' => $x, 'y' => $y, 'line_id' => 'L1'];

        // Right, down, left, up.
        $directions = [
            ['dx' => 1, 'dy' => 0],
            ['dx' => 0, 'dy' => 1],
            ['dx' => -1, 'dy' => 0],
            ['dx' => 0, 'dy' => -1],
        ];

        $segmentTarget = max(8, (int) ceil($stationCount / 4));
        $globalAttempts = 0;

        while (count($stationIds) < $stationCount && $globalAttempts < ($stationCount * 200)) {
            foreach ($directions as $direction) {
                $stepsThisSegment = $segmentTarget + $random->getInt(-2, 2);
                $stepsThisSegment = max(6, $stepsThisSegment);

                for ($step = 0; $step < $stepsThisSegment; $step++) {
                    if (count($stationIds) >= $stationCount) {
                        break 2;
                    }

                    $globalAttempts++;

                    $candidate = $this->nextCoordinate(
                        random: $random,
                        x: $x,
                        y: $y,
                        dx: $direction['dx'],
                        dy: $direction['dy'],
                        gridSize: $gridSize,
                        existingCoordinates: $stationCoordinates,
                    );

                    if ($candidate === null) {
                        continue;
                    }

                    $x = $candidate['x'];
                    $y = $candidate['y'];

                    $stationId = sprintf('S%d', $stationCounter++);
                    $stationIds[] = $stationId;
                    $stationCoordinates[$stationId] = ['x' => $x, 'y' => $y, 'line_id' => 'L1'];
                }
            }
        }

        if (count($stationIds) < $stationCount) {
            throw new InvalidArgumentException('Could not generate enough stations with current constraints.');
        }

        $hubCount = min(4, max(2, (int) round($stationCount * $this->hubRatio)));
        $hubIds = $this->pickDistributedHubIds($stationIds, $hubCount);

        $stations = [];

        foreach ($stationIds as $stationId) {
            $stations[$stationId] = new Station(
                id: $stationId,
                isHub: isset($hubIds[$stationId]),
            );
        }

        $edges = [];

        for ($i = 0; $i < count($stationIds) - 1; $i++) {
            $from = $stationIds[$i];
            $to = $stationIds[$i + 1];

            $edges[] = new Edge(
                fromStationId: $from,
                toStationId: $to,
                travelTimeSeconds: $random->getInt(2, 6),
                isExpress: false,
            );
        }

        // Close the loop.
        $edges[] = new Edge(
            fromStationId: $stationIds[count($stationIds) - 1],
            toStationId: $stationIds[0],
            travelTimeSeconds: $random->getInt(2, 6),
            isExpress: false,
        );

        return new GeneratedMap(
            stations: $stations,
            edges: $edges,
            stationCoordinates: $stationCoordinates,
        );
    }

    /**
     * @param array<string, array{x: int, y: int, line_id: string}> $existingCoordinates
     * @return array{x: int, y: int}|null
     */
    private function nextCoordinate(
        Randomizer $random,
        int $x,
        int $y,
        int $dx,
        int $dy,
        int $gridSize,
        array $existingCoordinates,
    ): ?array {
        for ($retry = 0; $retry < 8; $retry++) {
            $forward = $this->weightedForwardStep($random);
            $offset = $this->weightedOffset($random);
            $offsetSign = $random->getInt(0, 1) === 0 ? -1 : 1;

            $perpX = -$dy;
            $perpY = $dx;

            $candidateX = $x + ($dx * $forward) + ($perpX * $offset * $offsetSign);
            $candidateY = $y + ($dy * $forward) + ($perpY * $offset * $offsetSign);

            $candidateX = max(2, min($gridSize - 2, $candidateX));
            $candidateY = max(2, min($gridSize - 2, $candidateY));

            if ($this->hasEnoughSpacing($candidateX, $candidateY, $existingCoordinates)) {
                return ['x' => $candidateX, 'y' => $candidateY];
            }
        }

        return null;
    }

    private function weightedForwardStep(Randomizer $random): int
    {
        $bucket = [1, 1, 2, 2, 3, 3, 4, 5];

        return $bucket[$random->getInt(0, count($bucket) - 1)];
    }

    private function weightedOffset(Randomizer $random): int
    {
        $bucket = [0, 0, 0, 1, 1, 2, 3];

        return $bucket[$random->getInt(0, count($bucket) - 1)];
    }

    /**
     * @param array<string, array{x: int, y: int, line_id: string}> $existingCoordinates
     */
    private function hasEnoughSpacing(int $x, int $y, array $existingCoordinates): bool
    {
        foreach ($existingCoordinates as $coordinate) {
            $distance = abs($coordinate['x'] - $x) + abs($coordinate['y'] - $y);

            if ($distance < 2) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<string> $orderedStationIds
     * @return array<string, true>
     */
    private function pickDistributedHubIds(array $orderedStationIds, int $hubCount): array
    {
        $count = count($orderedStationIds);
        $step = max(1, (int) floor($count / $hubCount));
        $hubs = [];

        for ($i = 0; $i < $hubCount; $i++) {
            $index = ($i * $step) % $count;
            $hubs[$orderedStationIds[$index]] = true;
        }

        return $hubs;
    }
}

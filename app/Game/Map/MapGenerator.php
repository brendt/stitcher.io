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
    private const MAX_BRANCHES = 2;

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
        $mainLineStationIds = [];
        $edgeKeys = [];
        $edges = [];
        $lineCounter = 1;

        $stationCounter = 1;
        $firstId = sprintf('S%d', $stationCounter++);
        $stationIds[] = $firstId;
        $mainLineStationIds[] = $firstId;
        $stationCoordinates[$firstId] = ['x' => $x, 'y' => $y, 'line_id' => 'L1'];
        $lastMainStationId = $firstId;

        // Right, down, left, up.
        $directions = [
            ['dx' => 1, 'dy' => 0],
            ['dx' => 0, 'dy' => 1],
            ['dx' => -1, 'dy' => 0],
            ['dx' => 0, 'dy' => -1],
        ];

        $reservedForBranches = max(4, min(12, (int) floor($stationCount * 0.2)));
        $mainTarget = max(8, $stationCount - $reservedForBranches);
        $segmentTarget = max(8, (int) ceil($mainTarget / 4));
        $globalAttempts = 0;

        while (count($mainLineStationIds) < $mainTarget && $globalAttempts < ($stationCount * 200)) {
            foreach ($directions as $direction) {
                $stepsThisSegment = $segmentTarget + $random->getInt(-2, 2);
                $stepsThisSegment = max(6, $stepsThisSegment);

                for ($step = 0; $step < $stepsThisSegment; $step++) {
                    if (count($mainLineStationIds) >= $mainTarget) {
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
                    $mainLineStationIds[] = $stationId;
                    $stationCoordinates[$stationId] = ['x' => $x, 'y' => $y, 'line_id' => 'L1'];
                    $lastMainStationId = $stationId;
                }
            }
        }

        if (count($mainLineStationIds) < 8) {
            throw new InvalidArgumentException('Could not generate enough main-line stations with current constraints.');
        }

        $hubCount = min(4, max(2, (int) round($stationCount * $this->hubRatio)));
        $hubIds = $this->pickDistributedHubIds($mainLineStationIds, $hubCount);

        $branchesCreated = 0;

        foreach (array_keys($hubIds) as $splitStationId) {
            if (count($stationIds) >= $stationCount || $branchesCreated >= self::MAX_BRANCHES) {
                break;
            }

            $lineCounter++;
            $created = $this->generateBranch(
                random: $random,
                gridSize: $gridSize,
                splitStationId: $splitStationId,
                mainLineStationIds: $mainLineStationIds,
                remainingStations: $stationCount - count($stationIds),
                stationCounter: $stationCounter,
                stationIds: $stationIds,
                stationCoordinates: $stationCoordinates,
                edges: $edges,
                edgeKeys: $edgeKeys,
                lineId: sprintf('L%d', $lineCounter),
            );

            if ($created) {
                $branchesCreated++;
            }
        }

        $fillAttempts = 0;

        while (count($stationIds) < $stationCount && $fillAttempts < ($stationCount * 300)) {
            $fillAttempts++;

            $direction = $directions[$random->getInt(0, count($directions) - 1)];
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
            $mainLineStationIds[] = $stationId;
            $stationCoordinates[$stationId] = ['x' => $x, 'y' => $y, 'line_id' => 'L1'];
            $lastMainStationId = $stationId;
        }

        if (count($stationIds) < $stationCount) {
            throw new InvalidArgumentException('Could not generate enough stations with current constraints.');
        }

        for ($i = 0; $i < count($mainLineStationIds) - 1; $i++) {
            $this->addEdge(
                edges: $edges,
                edgeKeys: $edgeKeys,
                random: $random,
                from: $mainLineStationIds[$i],
                to: $mainLineStationIds[$i + 1],
                stationCoordinates: $stationCoordinates,
            );
        }

        // Keep at least one closed loop on the main line.
        $this->addEdge(
            edges: $edges,
            edgeKeys: $edgeKeys,
            random: $random,
            from: $lastMainStationId,
            to: $mainLineStationIds[0],
            stationCoordinates: $stationCoordinates,
        );

        $stations = [];

        foreach ($stationIds as $stationId) {
            $stations[$stationId] = new Station(
                id: $stationId,
                isHub: isset($hubIds[$stationId]),
            );
        }

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
     * @param list<string> $mainLineStationIds
     * @param list<string> $stationIds
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     * @param list<Edge> $edges
     * @param array<string, true> $edgeKeys
     */
    private function generateBranch(
        Randomizer $random,
        int $gridSize,
        string $splitStationId,
        array $mainLineStationIds,
        int $remainingStations,
        int &$stationCounter,
        array &$stationIds,
        array &$stationCoordinates,
        array &$edges,
        array &$edgeKeys,
        string $lineId,
    ): bool {
        if ($remainingStations < 3) {
            return false;
        }

        $split = $stationCoordinates[$splitStationId];
        $currentX = $split['x'];
        $currentY = $split['y'];
        ['dx' => $dx, 'dy' => $dy] = $this->directionTowardCenter($currentX, $currentY, $gridSize);

        $previousId = $splitStationId;
        $stationsSinceSplit = 0;
        $consecutiveFailures = 0;
        $branchTarget = min($remainingStations, $random->getInt(8, 18));
        $createdStations = 0;

        while ($stationsSinceSplit < $branchTarget) {
            $candidate = $stationsSinceSplit === 0
                ? $this->initialBranchCoordinate(
                    random: $random,
                    x: $currentX,
                    y: $currentY,
                    dx: $dx,
                    dy: $dy,
                    gridSize: $gridSize,
                    existingCoordinates: $stationCoordinates,
                )
                : $this->nextCoordinate(
                    random: $random,
                    x: $currentX,
                    y: $currentY,
                    dx: $dx,
                    dy: $dy,
                    gridSize: $gridSize,
                    existingCoordinates: $stationCoordinates,
                );

            if ($candidate === null) {
                $consecutiveFailures++;

                if ($consecutiveFailures >= 5) {
                    return $createdStations > 0;
                }

                ['dx' => $dx, 'dy' => $dy] = $this->rotateDirection(dx: $dx, dy: $dy, random: $random);
                continue;
            }

            $consecutiveFailures = 0;
            $currentX = $candidate['x'];
            $currentY = $candidate['y'];

            $newStationId = sprintf('S%d', $stationCounter++);
            $stationIds[] = $newStationId;
            $stationCoordinates[$newStationId] = ['x' => $currentX, 'y' => $currentY, 'line_id' => $lineId];
            $edgeAdded = $this->addEdge(
                edges: $edges,
                edgeKeys: $edgeKeys,
                random: $random,
                from: $previousId,
                to: $newStationId,
                stationCoordinates: $stationCoordinates,
                avoidOverlaps: true,
            );

            if (! $edgeAdded) {
                unset($stationCoordinates[$newStationId]);
                array_pop($stationIds);
                $stationCounter--;
                $consecutiveFailures++;
                continue;
            }

            $createdStations++;

            $previousId = $newStationId;
            $stationsSinceSplit++;

            if ($random->getInt(1, 100) <= 20) {
                ['dx' => $dx, 'dy' => $dy] = $this->rotateDirection(dx: $dx, dy: $dy, random: $random);
            }

            $mergeChance = min(45, 3 * $stationsSinceSplit);

            if ($stationsSinceSplit >= 5 && $random->getInt(1, 100) <= $mergeChance) {
                $mergeTarget = $this->findMergeTarget(
                    random: $random,
                    currentStationId: $newStationId,
                    splitStationId: $splitStationId,
                    mainLineStationIds: $mainLineStationIds,
                    stationCoordinates: $stationCoordinates,
                    edgeKeys: $edgeKeys,
                );

                if ($mergeTarget !== null) {
                    $merged = $this->addEdge(
                        edges: $edges,
                        edgeKeys: $edgeKeys,
                        random: $random,
                        from: $newStationId,
                        to: $mergeTarget,
                        stationCoordinates: $stationCoordinates,
                        avoidOverlaps: true,
                    );

                    if ($merged) {
                        return true;
                    }
                }
            }

            if ($this->isNearBoundary(x: $currentX, y: $currentY, gridSize: $gridSize, margin: 2)) {
                return $createdStations > 0;
            }
        }

        return $createdStations > 0;
    }

    /**
     * @param array<string, array{x: int, y: int, line_id: string}> $existingCoordinates
     * @return array{x: int, y: int}|null
     */
    private function initialBranchCoordinate(
        Randomizer $random,
        int $x,
        int $y,
        int $dx,
        int $dy,
        int $gridSize,
        array $existingCoordinates,
    ): ?array {
        for ($retry = 0; $retry < 8; $retry++) {
            $forward = $random->getInt(4, 8);
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

    /**
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     * @param list<string> $mainLineStationIds
     * @param array<string, true> $edgeKeys
     */
    private function findMergeTarget(
        Randomizer $random,
        string $currentStationId,
        string $splitStationId,
        array $mainLineStationIds,
        array $stationCoordinates,
        array $edgeKeys,
    ): ?string {
        $current = $stationCoordinates[$currentStationId];
        $candidates = [];

        foreach ($mainLineStationIds as $candidateStationId) {
            if ($candidateStationId === $splitStationId || $candidateStationId === $currentStationId) {
                continue;
            }

            $key = $this->edgeKey($currentStationId, $candidateStationId);

            if (isset($edgeKeys[$key])) {
                continue;
            }

            $candidate = $stationCoordinates[$candidateStationId];
            $distance = abs($candidate['x'] - $current['x']) + abs($candidate['y'] - $current['y']);

            if ($distance < 6 || $distance > 40) {
                continue;
            }

            $candidates[] = ['stationId' => $candidateStationId, 'distance' => $distance];
        }

        if ($candidates === []) {
            return null;
        }

        usort(
            $candidates,
            static fn (array $a, array $b): int => $a['distance'] <=> $b['distance'],
        );

        $top = array_slice($candidates, 0, min(3, count($candidates)));

        return $top[$random->getInt(0, count($top) - 1)]['stationId'];
    }

    /**
     * @return array{dx: int, dy: int}
     */
    private function directionTowardCenter(int $x, int $y, int $gridSize): array
    {
        $center = (int) floor($gridSize / 2);
        $deltaX = $center - $x;
        $deltaY = $center - $y;

        if (abs($deltaX) >= abs($deltaY)) {
            return ['dx' => $deltaX >= 0 ? 1 : -1, 'dy' => 0];
        }

        return ['dx' => 0, 'dy' => $deltaY >= 0 ? 1 : -1];
    }

    /**
     * @return array{dx: int, dy: int}
     */
    private function rotateDirection(int $dx, int $dy, Randomizer $random): array
    {
        if ($random->getInt(0, 1) === 0) {
            return ['dx' => -$dy, 'dy' => $dx];
        }

        return ['dx' => $dy, 'dy' => -$dx];
    }

    private function isNearBoundary(int $x, int $y, int $gridSize, int $margin = 3): bool
    {
        return $x <= $margin || $y <= $margin || $x >= ($gridSize - $margin) || $y >= ($gridSize - $margin);
    }

    /**
     * @param list<Edge> $edges
     * @param array<string, true> $edgeKeys
     */
    private function addEdge(
        array &$edges,
        array &$edgeKeys,
        Randomizer $random,
        string $from,
        string $to,
        array $stationCoordinates,
        bool $avoidOverlaps = false,
    ): bool
    {
        $key = $this->edgeKey($from, $to);

        if (isset($edgeKeys[$key])) {
            return false;
        }

        if ($avoidOverlaps && $this->wouldOverlapExistingEdge(
            from: $from,
            to: $to,
            stationCoordinates: $stationCoordinates,
            edges: $edges,
        )) {
            return false;
        }

        $edges[] = new Edge(
            fromStationId: $from,
            toStationId: $to,
            travelTimeSeconds: $random->getInt(2, 6),
            isExpress: false,
        );

        $edgeKeys[$key] = true;

        return true;
    }

    private function edgeKey(string $a, string $b): string
    {
        return $a < $b ? sprintf('%s|%s', $a, $b) : sprintf('%s|%s', $b, $a);
    }

    /**
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     * @param list<Edge> $edges
     */
    private function wouldOverlapExistingEdge(string $from, string $to, array $stationCoordinates, array $edges): bool
    {
        $a1 = $stationCoordinates[$from] ?? null;
        $a2 = $stationCoordinates[$to] ?? null;

        if ($a1 === null || $a2 === null) {
            return true;
        }

        foreach ($edges as $edge) {
            if (
                $edge->fromStationId === $from
                || $edge->toStationId === $from
                || $edge->fromStationId === $to
                || $edge->toStationId === $to
            ) {
                // Shared endpoint is allowed.
                continue;
            }

            $b1 = $stationCoordinates[$edge->fromStationId] ?? null;
            $b2 = $stationCoordinates[$edge->toStationId] ?? null;

            if ($b1 === null || $b2 === null) {
                continue;
            }

            if ($this->segmentsIntersect($a1, $a2, $b1, $b2)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array{x: int, y: int} $p1
     * @param array{x: int, y: int} $q1
     * @param array{x: int, y: int} $p2
     * @param array{x: int, y: int} $q2
     */
    private function segmentsIntersect(array $p1, array $q1, array $p2, array $q2): bool
    {
        $o1 = $this->orientation($p1, $q1, $p2);
        $o2 = $this->orientation($p1, $q1, $q2);
        $o3 = $this->orientation($p2, $q2, $p1);
        $o4 = $this->orientation($p2, $q2, $q1);

        if ($o1 !== $o2 && $o3 !== $o4) {
            return true;
        }

        if ($o1 === 0 && $this->onSegment($p1, $p2, $q1)) {
            return true;
        }

        if ($o2 === 0 && $this->onSegment($p1, $q2, $q1)) {
            return true;
        }

        if ($o3 === 0 && $this->onSegment($p2, $p1, $q2)) {
            return true;
        }

        return $o4 === 0 && $this->onSegment($p2, $q1, $q2);
    }

    /**
     * @param array{x: int, y: int} $p
     * @param array{x: int, y: int} $q
     * @param array{x: int, y: int} $r
     */
    private function orientation(array $p, array $q, array $r): int
    {
        $value = (($q['y'] - $p['y']) * ($r['x'] - $q['x'])) - (($q['x'] - $p['x']) * ($r['y'] - $q['y']));

        if ($value === 0) {
            return 0;
        }

        return $value > 0 ? 1 : 2;
    }

    /**
     * @param array{x: int, y: int} $p
     * @param array{x: int, y: int} $q
     * @param array{x: int, y: int} $r
     */
    private function onSegment(array $p, array $q, array $r): bool
    {
        return $q['x'] <= max($p['x'], $r['x'])
            && $q['x'] >= min($p['x'], $r['x'])
            && $q['y'] <= max($p['y'], $r['y'])
            && $q['y'] >= min($p['y'], $r['y']);
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

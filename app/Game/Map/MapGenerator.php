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
    private const MAX_STATION_COUNT = 200;
    private const MIN_STATION_DISTANCE = 3;
    private const MIN_INTERSECTION_STATION_DISTANCE = 5;
    private const MIN_LINE_STATIONS = 4;

    public function generate(int $stationCount, int $seed, int $playerCount = 2): GeneratedMap
    {
        if ($stationCount < 8) {
            throw new InvalidArgumentException('Station count must be at least 8.');
        }
        if ($stationCount > self::MAX_STATION_COUNT) {
            throw new InvalidArgumentException(sprintf('Station count must be at most %d.', self::MAX_STATION_COUNT));
        }
        if ($playerCount < 2 || $playerCount > 6) {
            throw new InvalidArgumentException('Player count must be between 2 and 6.');
        }
        if ($stationCount < ($playerCount * self::MIN_LINE_STATIONS)) {
            throw new InvalidArgumentException(sprintf(
                'Station count must be at least %d for %d players.',
                $playerCount * self::MIN_LINE_STATIONS,
                $playerCount,
            ));
        }

        $random = new Randomizer(new Mt19937($seed));
        $lineStationTargets = $this->lineStationTargets($stationCount, $playerCount);
        $maxLoopStations = max($lineStationTargets);
        $requiredRadius = (int) ceil(($maxLoopStations * self::MIN_STATION_DISTANCE * 1.18) / (2 * M_PI));
        $radius = max(14, $requiredRadius);
        $baseGridSize = $playerCount <= 2 ? 100 : 120;
        $stationScale = sqrt(max(1, $stationCount / 60));
        $maxCenterSpread = (int) ceil(($playerCount - 1) * 15);
        $minGridSize = (2 * ($radius + 6)) + $maxCenterSpread + 8;
        $gridSize = (int) ceil(max($baseGridSize * $stationScale, $minGridSize));

        $stationCoordinates = [];
        $stationIds = [];
        $loopStationIds = [];
        $generated = false;

        for ($layoutAttempt = 0; $layoutAttempt < 18; $layoutAttempt++) {
            $stationCoordinates = [];
            $stationIds = [];
            $loopStationIds = [];
            $stationCounter = 1;
            $radiusForAttempt = max(12, $radius - intdiv($layoutAttempt, 4));
            $midpoint = (int) floor($gridSize / 2);
            $minCenter = $radiusForAttempt + 6;
            $maxCenter = $gridSize - $radiusForAttempt - 6;
            $centerStepAxes = [];
            $offsets = [['x' => 0, 'y' => 0]];
            $currentOffsetX = 0;
            $currentOffsetY = 0;
            for ($stepIndex = 0; $stepIndex < ($playerCount - 1); $stepIndex++) {
                $centerStepAxes[] = $random->getInt(0, 1) === 1;
            }

            if (
                $playerCount > 2
                && (count(array_unique($centerStepAxes)) === 1)
            ) {
                $flipIndex = $random->getInt(0, count($centerStepAxes) - 1);
                $centerStepAxes[$flipIndex] = ! $centerStepAxes[$flipIndex];
            }

            for ($stepIndex = 0; $stepIndex < ($playerCount - 1); $stepIndex++) {
                $step = $random->getInt(15, 30);
                if ($centerStepAxes[$stepIndex]) {
                    $currentOffsetX += $step;
                } else {
                    $currentOffsetY += $step;
                }

                $offsets[] = ['x' => $currentOffsetX, 'y' => $currentOffsetY];
            }

            $offsetXs = array_column($offsets, 'x');
            $offsetYs = array_column($offsets, 'y');
            $baseCenterX = $midpoint - (int) round(((min($offsetXs) + max($offsetXs)) / 2));
            $baseCenterY = $midpoint - (int) round(((min($offsetYs) + max($offsetYs)) / 2));

            $layoutValid = true;
            for ($lineIndex = 0; $lineIndex < $playerCount; $lineIndex++) {
                $lineId = sprintf('L%d', $lineIndex + 1);
                $targetCount = $lineStationTargets[$lineIndex];
                $centerX = $baseCenterX + $offsets[$lineIndex]['x'];
                $centerY = $baseCenterY + $offsets[$lineIndex]['y'];
                $centerX = max($minCenter, min($maxCenter, $centerX));
                $centerY = max($minCenter, min($maxCenter, $centerY));

                $lineStations = $this->generateCircularLine(
                    random: $random,
                    gridSize: $gridSize,
                    lineId: $lineId,
                    targetCount: $targetCount,
                    centerX: $centerX,
                    centerY: $centerY,
                    radius: $radiusForAttempt,
                    sharedHubIds: [],
                    stationCounter: $stationCounter,
                    stationIds: $stationIds,
                    stationCoordinates: $stationCoordinates,
                );

                if ($lineStations === []) {
                    $layoutValid = false;
                    break;
                }

                $loopStationIds[] = $lineStations;
            }

            if (! $layoutValid || count($stationIds) !== $stationCount) {
                continue;
            }

            $generated = true;
            break;
        }

        if (! $generated) {
            throw new InvalidArgumentException('Could not generate looped lines with current constraints.');
        }

        $edgeKeys = [];
        $edges = [];
        foreach ($loopStationIds as $lineStationIds) {
            for ($i = 0; $i < count($lineStationIds) - 1; $i++) {
                $this->addEdge(
                    edges: $edges,
                    edgeKeys: $edgeKeys,
                    random: $random,
                    from: $lineStationIds[$i],
                    to: $lineStationIds[$i + 1],
                    stationCoordinates: $stationCoordinates,
                );
            }

            $this->closeLoop(
                random: $random,
                lineStationIds: $lineStationIds,
                stationCoordinates: $stationCoordinates,
                edges: $edges,
                edgeKeys: $edgeKeys,
            );
        }

        for ($i = 0; $i < count($loopStationIds) - 1; $i++) {
            $bridge = $this->nearestStationPair(
                firstLineStationIds: $loopStationIds[$i],
                secondLineStationIds: $loopStationIds[$i + 1],
                stationCoordinates: $stationCoordinates,
            );

            if ($bridge === null) {
                continue;
            }

            $added = $this->addEdge(
                edges: $edges,
                edgeKeys: $edgeKeys,
                random: $random,
                from: $bridge['from'],
                to: $bridge['to'],
                stationCoordinates: $stationCoordinates,
                avoidOverlaps: true,
            );

            if (! $added) {
                $this->addEdge(
                    edges: $edges,
                    edgeKeys: $edgeKeys,
                    random: $random,
                    from: $bridge['from'],
                    to: $bridge['to'],
                    stationCoordinates: $stationCoordinates,
                );
            }
        }

        $stationCounter = count($stationIds) + 1;
        $this->promoteEdgeIntersectionsToStations(
            edges: $edges,
            stationIds: $stationIds,
            stationCoordinates: $stationCoordinates,
            stationCounter: $stationCounter,
        );

        $stations = [];

        foreach ($stationIds as $stationId) {
            $stations[$stationId] = new Station(
                id: $stationId,
                isHub: false,
            );
        }

        return new GeneratedMap(
            stations: $stations,
            edges: $edges,
            stationCoordinates: $stationCoordinates,
        );
    }

    /**
     * @return list<int>
     */
    private function lineStationTargets(int $stationCount, int $playerCount): array
    {
        $targets = array_fill(0, $playerCount, intdiv($stationCount, $playerCount));

        for ($index = 0; $index < ($stationCount % $playerCount); $index++) {
            $targets[$index]++;
        }

        return $targets;
    }

    /**
     * @param list<string> $sharedHubIds
     * @param list<string> $stationIds
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     * @return list<string>
     */
    private function generateCircularLine(
        Randomizer $random,
        int $gridSize,
        string $lineId,
        int $targetCount,
        int $centerX,
        int $centerY,
        int $radius,
        array $sharedHubIds,
        int &$stationCounter,
        array &$stationIds,
        array &$stationCoordinates,
    ): array {
        if ($targetCount < max(self::MIN_LINE_STATIONS, count($sharedHubIds) + 2)) {
            $targetCount = max(self::MIN_LINE_STATIONS, count($sharedHubIds) + 2);
        }

        $orderedStationIds = array_fill(0, $targetCount, null);
        $reservedIndices = [];

        foreach ($sharedHubIds as $hubId) {
            $hub = $stationCoordinates[$hubId] ?? null;
            if ($hub === null) {
                continue;
            }

            $angle = atan2((float) ($hub['y'] - $centerY), (float) ($hub['x'] - $centerX));
            if ($angle < 0) {
                $angle += 2 * M_PI;
            }

            $approximateIndex = (int) round(($angle / (2 * M_PI)) * $targetCount) % $targetCount;

            for ($offset = 0; $offset < $targetCount; $offset++) {
                $index = ($approximateIndex + $offset) % $targetCount;
                if (!isset($reservedIndices[$index])) {
                    $orderedStationIds[$index] = $hubId;
                    $reservedIndices[$index] = true;
                    break;
                }
            }
        }

        for ($index = 0; $index < $targetCount; $index++) {
            if ($orderedStationIds[$index] !== null) {
                continue;
            }

            $baseAngle = (2 * M_PI * $index) / $targetCount;
            $prevIndex = ($index - 1 + $targetCount) % $targetCount;
            $prevBaseAngle = (2 * M_PI * $prevIndex) / $targetCount;
            $prevBaseX = (int) round($centerX + (cos($prevBaseAngle) * $radius));
            $prevBaseY = (int) round($centerY + (sin($prevBaseAngle) * $radius));
            $placed = false;

            $baseX = (int) round($centerX + (cos($baseAngle) * $radius));
            $baseY = (int) round($centerY + (sin($baseAngle) * $radius));
            $stepX = $baseX - $prevBaseX;
            $stepY = $baseY - $prevBaseY;
            $previousStationId = $orderedStationIds[$prevIndex];
            $previousActual = $previousStationId !== null ? ($stationCoordinates[$previousStationId] ?? null) : null;
            $anchorX = $previousActual['x'] ?? $prevBaseX;
            $anchorY = $previousActual['y'] ?? $prevBaseY;
            $expectedStepMagnitudeSquared = ($stepX * $stepX) + ($stepY * $stepY);
            $prevPrevIndex = ($prevIndex - 1 + $targetCount) % $targetCount;
            $prevPrevStationId = $orderedStationIds[$prevPrevIndex];
            $prevPrevActual = $prevPrevStationId !== null ? ($stationCoordinates[$prevPrevStationId] ?? null) : null;
            $prevActualStepX = $prevPrevActual !== null ? ($anchorX - $prevPrevActual['x']) : null;
            $prevActualStepY = $prevPrevActual !== null ? ($anchorY - $prevPrevActual['y']) : null;

            for ($retry = 0; $retry < 60; $retry++) {
                $xVariance = $random->getInt(-3, 3);
                $yVariance = $random->getInt(-3, 3);
                $x = (int) round($anchorX + $stepX + $xVariance);
                $y = (int) round($anchorY + $stepY + $yVariance);
                $candidateStepX = $x - $anchorX;
                $candidateStepY = $y - $anchorY;

                if ($expectedStepMagnitudeSquared > 0) {
                    // Prevent reverse moves: candidate must keep moving in the loop's local direction.
                    $forwardProjection = ($candidateStepX * $stepX) + ($candidateStepY * $stepY);
                    if ($forwardProjection <= 0) {
                        continue;
                    }
                }

                if ($prevActualStepX !== null && $prevActualStepY !== null) {
                    // Prevent jagged backtracking between consecutive segments.
                    $turnProjection = ($candidateStepX * $prevActualStepX) + ($candidateStepY * $prevActualStepY);
                    $prevStepMagnitudeSquared = ($prevActualStepX * $prevActualStepX) + ($prevActualStepY * $prevActualStepY);
                    $allowedBacktrack = $prevStepMagnitudeSquared * -0.25;
                    if ($turnProjection < $allowedBacktrack) {
                        continue;
                    }
                }

                // Keep a loose circular envelope, but allow visible wobble.
                $distanceFromCenter = sqrt((($x - $centerX) ** 2) + (($y - $centerY) ** 2));
                if (abs($distanceFromCenter - $radius) > 8) {
                    continue;
                }

                $x = max(2, min($gridSize - 2, $x));
                $y = max(2, min($gridSize - 2, $y));

                if (! $this->hasEnoughSpacing($x, $y, $stationCoordinates)) {
                    continue;
                }

                $stationId = sprintf('S%d', $stationCounter++);
                $stationIds[] = $stationId;
                $stationCoordinates[$stationId] = ['x' => $x, 'y' => $y, 'line_id' => $lineId];
                $orderedStationIds[$index] = $stationId;
                $placed = true;
                break;
            }

            if (! $placed) {
                return [];
            }
        }

        /** @var list<string> $lineStationIds */
        $lineStationIds = array_values(array_filter($orderedStationIds, static fn (?string $id): bool => $id !== null));

        return $lineStationIds;
    }

    /**
     * @param list<string> $firstLineStationIds
     * @param list<string> $secondLineStationIds
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     * @return array{from: string, to: string}|null
     */
    private function nearestStationPair(array $firstLineStationIds, array $secondLineStationIds, array $stationCoordinates): ?array
    {
        $bestPair = null;
        $bestDistance = null;

        foreach ($firstLineStationIds as $from) {
            $fromCoordinate = $stationCoordinates[$from] ?? null;
            if ($fromCoordinate === null) {
                continue;
            }

            foreach ($secondLineStationIds as $to) {
                $toCoordinate = $stationCoordinates[$to] ?? null;
                if ($toCoordinate === null) {
                    continue;
                }

                $distance = abs($fromCoordinate['x'] - $toCoordinate['x']) + abs($fromCoordinate['y'] - $toCoordinate['y']);
                if ($bestDistance === null || $distance < $bestDistance) {
                    $bestDistance = $distance;
                    $bestPair = ['from' => $from, 'to' => $to];
                }
            }
        }

        return $bestPair;
    }

    /**
     * @param list<Edge> $edges
     * @param list<string> $stationIds
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     */
    private function promoteEdgeIntersectionsToStations(
        array &$edges,
        array &$stationIds,
        array &$stationCoordinates,
        int &$stationCounter,
    ): void {
        $intersectionStationIds = [];

        for ($pass = 0; $pass < 200; $pass++) {
            $splitApplied = false;
            $edgeCount = count($edges);

            for ($i = 0; $i < $edgeCount; $i++) {
                for ($j = $i + 1; $j < $edgeCount; $j++) {
                    $first = $edges[$i];
                    $second = $edges[$j];

                    if (
                        $first->fromStationId === $second->fromStationId
                        || $first->fromStationId === $second->toStationId
                        || $first->toStationId === $second->fromStationId
                        || $first->toStationId === $second->toStationId
                    ) {
                        continue;
                    }

                    $intersection = $this->edgeIntersectionPoint($first, $second, $stationCoordinates);
                    if ($intersection === null) {
                        continue;
                    }

                    $x = (int) round($intersection['x']);
                    $y = (int) round($intersection['y']);
                    $x = max(2, $x);
                    $y = max(2, $y);
                    $stationId = $this->stationAtCoordinate($x, $y, $stationCoordinates);
                    $stationId ??= $this->nearestStationWithinDistance(
                        x: $x,
                        y: $y,
                        stationCoordinates: $stationCoordinates,
                        maxDistance: self::MIN_INTERSECTION_STATION_DISTANCE,
                    );

                    if ($stationId === null) {
                        if (! $this->hasEnoughSpacing($x, $y, $stationCoordinates)) {
                            continue;
                        }

                        if (! $this->hasEnoughIntersectionSpacing($x, $y, $intersectionStationIds, $stationCoordinates)) {
                            continue;
                        }

                        $stationId = sprintf('S%d', $stationCounter++);
                        $stationIds[] = $stationId;
                        $lineId = $stationCoordinates[$first->fromStationId]['line_id'] ?? 'L1';
                        $stationCoordinates[$stationId] = ['x' => $x, 'y' => $y, 'line_id' => $lineId];
                        $intersectionStationIds[] = $stationId;
                    }

                    $rebuilt = [];
                    $edgeKeys = [];

                    foreach ($edges as $index => $edge) {
                        if ($index === $i || $index === $j) {
                            foreach ($this->splitEdgeThroughStation($edge, $stationId, $stationCoordinates) as $split) {
                                $this->appendUniqueEdge($rebuilt, $edgeKeys, $split);
                            }
                            continue;
                        }

                        $this->appendUniqueEdge($rebuilt, $edgeKeys, $edge);
                    }

                    $edges = $rebuilt;
                    $splitApplied = true;
                    break 2;
                }
            }

            if (! $splitApplied) {
                return;
            }
        }
    }

    /**
     * @param list<string> $intersectionStationIds
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     */
    private function hasEnoughIntersectionSpacing(int $x, int $y, array $intersectionStationIds, array $stationCoordinates): bool
    {
        foreach ($intersectionStationIds as $stationId) {
            $coordinate = $stationCoordinates[$stationId] ?? null;
            if ($coordinate === null) {
                continue;
            }

            $distance = abs($coordinate['x'] - $x) + abs($coordinate['y'] - $y);
            if ($distance < self::MIN_INTERSECTION_STATION_DISTANCE) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     * @return array{x: float, y: float}|null
     */
    private function edgeIntersectionPoint(Edge $first, Edge $second, array $stationCoordinates): ?array
    {
        $a1 = $stationCoordinates[$first->fromStationId] ?? null;
        $a2 = $stationCoordinates[$first->toStationId] ?? null;
        $b1 = $stationCoordinates[$second->fromStationId] ?? null;
        $b2 = $stationCoordinates[$second->toStationId] ?? null;
        if ($a1 === null || $a2 === null || $b1 === null || $b2 === null) {
            return null;
        }

        $denominator = (($a1['x'] - $a2['x']) * ($b1['y'] - $b2['y'])) - (($a1['y'] - $a2['y']) * ($b1['x'] - $b2['x']));
        if (abs($denominator) < 1e-9) {
            return null;
        }

        $detA = ($a1['x'] * $a2['y']) - ($a1['y'] * $a2['x']);
        $detB = ($b1['x'] * $b2['y']) - ($b1['y'] * $b2['x']);
        $x = (($detA * ($b1['x'] - $b2['x'])) - (($a1['x'] - $a2['x']) * $detB)) / $denominator;
        $y = (($detA * ($b1['y'] - $b2['y'])) - (($a1['y'] - $a2['y']) * $detB)) / $denominator;

        if (
            ! $this->pointWithinSegmentBounds($x, $y, $a1, $a2)
            || ! $this->pointWithinSegmentBounds($x, $y, $b1, $b2)
        ) {
            return null;
        }

        if (
            $this->pointAtSegmentEndpoint($x, $y, $a1, $a2)
            || $this->pointAtSegmentEndpoint($x, $y, $b1, $b2)
        ) {
            return null;
        }

        return ['x' => $x, 'y' => $y];
    }

    /**
     * @param array{x: int, y: int} $from
     * @param array{x: int, y: int} $to
     */
    private function pointWithinSegmentBounds(float $x, float $y, array $from, array $to): bool
    {
        return $x >= (min($from['x'], $to['x']) - 0.001)
            && $x <= (max($from['x'], $to['x']) + 0.001)
            && $y >= (min($from['y'], $to['y']) - 0.001)
            && $y <= (max($from['y'], $to['y']) + 0.001);
    }

    /**
     * @param array{x: int, y: int} $from
     * @param array{x: int, y: int} $to
     */
    private function pointAtSegmentEndpoint(float $x, float $y, array $from, array $to): bool
    {
        return (abs($x - $from['x']) < 0.001 && abs($y - $from['y']) < 0.001)
            || (abs($x - $to['x']) < 0.001 && abs($y - $to['y']) < 0.001);
    }

    /**
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     */
    private function stationAtCoordinate(int $x, int $y, array $stationCoordinates): ?string
    {
        foreach ($stationCoordinates as $stationId => $coordinate) {
            if ($coordinate['x'] === $x && $coordinate['y'] === $y) {
                return $stationId;
            }
        }

        return null;
    }

    /**
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     */
    private function nearestStationWithinDistance(int $x, int $y, array $stationCoordinates, int $maxDistance): ?string
    {
        $closestStationId = null;
        $closestDistance = null;

        foreach ($stationCoordinates as $stationId => $coordinate) {
            $distance = abs($coordinate['x'] - $x) + abs($coordinate['y'] - $y);
            if ($distance > $maxDistance) {
                continue;
            }

            if ($closestDistance === null || $distance < $closestDistance) {
                $closestDistance = $distance;
                $closestStationId = $stationId;
            }
        }

        return $closestStationId;
    }

    /**
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     * @return list<Edge>
     */
    private function splitEdgeThroughStation(Edge $edge, string $stationId, array $stationCoordinates): array
    {
        if ($edge->fromStationId === $stationId || $edge->toStationId === $stationId) {
            return [$edge];
        }

        if (! isset($stationCoordinates[$edge->fromStationId], $stationCoordinates[$edge->toStationId], $stationCoordinates[$stationId])) {
            return [$edge];
        }

        return [
            new Edge(
                fromStationId: $edge->fromStationId,
                toStationId: $stationId,
                travelTimeSeconds: $edge->travelTimeSeconds,
                isExpress: $edge->isExpress,
            ),
            new Edge(
                fromStationId: $stationId,
                toStationId: $edge->toStationId,
                travelTimeSeconds: $edge->travelTimeSeconds,
                isExpress: $edge->isExpress,
            ),
        ];
    }

    /**
     * @param list<Edge> $edges
     * @param array<string, true> $edgeKeys
     */
    private function appendUniqueEdge(array &$edges, array &$edgeKeys, Edge $edge): void
    {
        $key = $this->edgeKey($edge->fromStationId, $edge->toStationId);
        if (isset($edgeKeys[$key])) {
            return;
        }

        $edgeKeys[$key] = true;
        $edges[] = $edge;
    }

    /**
     * @param list<string> $lineStationIds
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     * @param list<Edge> $edges
     * @param array<string, true> $edgeKeys
     */
    private function closeLoop(
        Randomizer $random,
        array $lineStationIds,
        array $stationCoordinates,
        array &$edges,
        array &$edgeKeys,
    ): void {
        if (count($lineStationIds) < 3) {
            return;
        }

        $first = $lineStationIds[0];
        $last = $lineStationIds[count($lineStationIds) - 1];

        $closed = $this->addEdge(
            edges: $edges,
            edgeKeys: $edgeKeys,
            random: $random,
            from: $last,
            to: $first,
            stationCoordinates: $stationCoordinates,
        );

        if ($closed) {
            return;
        }

        $candidatePairs = [];
        for ($i = 0; $i < count($lineStationIds); $i++) {
            for ($j = $i + 2; $j < count($lineStationIds); $j++) {
                $from = $lineStationIds[$i];
                $to = $lineStationIds[$j];
                $distance = abs($stationCoordinates[$from]['x'] - $stationCoordinates[$to]['x'])
                    + abs($stationCoordinates[$from]['y'] - $stationCoordinates[$to]['y']);
                $candidatePairs[] = ['from' => $from, 'to' => $to, 'distance' => $distance];
            }
        }

        usort(
            $candidatePairs,
            static fn (array $left, array $right): int => $right['distance'] <=> $left['distance'],
        );

        foreach ($candidatePairs as $pair) {
            $added = $this->addEdge(
                edges: $edges,
                edgeKeys: $edgeKeys,
                random: $random,
                from: $pair['from'],
                to: $pair['to'],
                stationCoordinates: $stationCoordinates,
            );

            if ($added) {
                return;
            }
        }
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

            if ($distance < self::MIN_STATION_DISTANCE) {
                return false;
            }
        }

        return true;
    }

}

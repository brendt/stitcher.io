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
    private const MIN_LINE_STATIONS = 4;

    public function generate(int $stationCount, int $seed, int $playerCount = 2): GeneratedMap
    {
        if ($stationCount < 8) {
            throw new InvalidArgumentException('Station count must be at least 8.');
        }
        if ($stationCount > self::MAX_STATION_COUNT) {
            throw new InvalidArgumentException(sprintf('Station count must be at most %d.', self::MAX_STATION_COUNT));
        }

        $baseGridSize = $playerCount <= 2 ? 100 : 120;
        $stationScale = sqrt(max(1, $stationCount / 60));
        $gridSize = (int) ceil($baseGridSize * $stationScale);

        $random = new Randomizer(new Mt19937($seed));

        $edgeKeys = [];
        $edges = [];
        $totalLoopSlots = $stationCount + 2; // Two shared hub stations appear in both loops.
        $mainTarget = max(self::MIN_LINE_STATIONS, (int) ceil($totalLoopSlots / 2));
        $secondaryTarget = $totalLoopSlots - $mainTarget;

        if ($secondaryTarget > 0 && $secondaryTarget < self::MIN_LINE_STATIONS) {
            $missing = self::MIN_LINE_STATIONS - $secondaryTarget;
            $mainTarget = max(self::MIN_LINE_STATIONS, $mainTarget - $missing);
            $secondaryTarget = $totalLoopSlots - $mainTarget;
        }

        $maxLoopStations = max($mainTarget, $secondaryTarget);
        $requiredRadius = (int) ceil(($maxLoopStations * self::MIN_STATION_DISTANCE * 1.18) / (2 * M_PI));
        $radius = max(14, min((int) floor($gridSize * 0.28), $requiredRadius));
        $stationCoordinates = [];
        $stationIds = [];
        $hubIds = [];
        $mainLineStationIds = [];
        $secondaryLineStationIds = [];
        $generated = false;

        for ($layoutAttempt = 0; $layoutAttempt < 10; $layoutAttempt++) {
            $stationCoordinates = [];
            $stationIds = [];
            $stationCounter = 1;
            $radiusForAttempt = max(12, $radius - $layoutAttempt);
            $centerY = max($radiusForAttempt + 6, min($gridSize - $radiusForAttempt - 6, (int) floor($gridSize * 0.5)));
            $maxCenterDistance = $gridSize - (2 * ($radiusForAttempt + 6));
            $centerDistance = min($random->getInt(8, 12), $maxCenterDistance);

            if ($centerDistance < (self::MIN_STATION_DISTANCE * 2)) {
                continue;
            }

            $center1X = (int) floor(($gridSize - $centerDistance) / 2);
            $center2X = $center1X + $centerDistance;

            if ($center2X <= $center1X) {
                continue;
            }

            $centerDistance = $center2X - $center1X;
            if ($centerDistance >= (2 * $radiusForAttempt)) {
                continue;
            }

            $intersectionHeight = (int) round(sqrt(max(1, ($radiusForAttempt * $radiusForAttempt) - (($centerDistance * $centerDistance) / 4))));
            $intersectionHeight = max((int) ceil(self::MIN_STATION_DISTANCE / 2), $intersectionHeight);
            $intersectionX = (int) round(($center1X + $center2X) / 2);
            $hubTopY = max(2, min($gridSize - 2, $centerY - $intersectionHeight));
            $hubBottomY = max(2, min($gridSize - 2, $centerY + $intersectionHeight));

            if (! $this->hasEnoughSpacing($intersectionX, $hubTopY, $stationCoordinates)) {
                continue;
            }

            if (! $this->hasEnoughSpacing($intersectionX, $hubBottomY, [
                'hub-top' => ['x' => $intersectionX, 'y' => $hubTopY, 'line_id' => 'L1'],
            ])) {
                continue;
            }

            $hubTopId = sprintf('S%d', $stationCounter++);
            $hubBottomId = sprintf('S%d', $stationCounter++);
            $stationIds[] = $hubTopId;
            $stationIds[] = $hubBottomId;
            $stationCoordinates[$hubTopId] = ['x' => $intersectionX, 'y' => $hubTopY, 'line_id' => 'L1'];
            $stationCoordinates[$hubBottomId] = ['x' => $intersectionX, 'y' => $hubBottomY, 'line_id' => 'L1'];

            $attemptHubIds = [$hubTopId => true, $hubBottomId => true];

            $attemptMainLineStationIds = $this->generateCircularLine(
                random: $random,
                gridSize: $gridSize,
                lineId: 'L1',
                targetCount: $mainTarget,
                centerX: $center1X,
                centerY: $centerY,
                radius: $radiusForAttempt,
                sharedHubIds: [$hubTopId, $hubBottomId],
                stationCounter: $stationCounter,
                stationIds: $stationIds,
                stationCoordinates: $stationCoordinates,
            );

            $attemptSecondaryLineStationIds = $this->generateCircularLine(
                random: $random,
                gridSize: $gridSize,
                lineId: 'L2',
                targetCount: $secondaryTarget,
                centerX: $center2X,
                centerY: $centerY,
                radius: $radiusForAttempt,
                sharedHubIds: [$hubTopId, $hubBottomId],
                stationCounter: $stationCounter,
                stationIds: $stationIds,
                stationCoordinates: $stationCoordinates,
            );

            if ($attemptMainLineStationIds === [] || $attemptSecondaryLineStationIds === []) {
                continue;
            }

            if (count($stationIds) !== $stationCount) {
                continue;
            }

            $hubIds = $attemptHubIds;
            $mainLineStationIds = $attemptMainLineStationIds;
            $secondaryLineStationIds = $attemptSecondaryLineStationIds;
            $generated = true;
            break;
        }

        if (! $generated) {
            throw new InvalidArgumentException('Could not generate two circular lines with current constraints.');
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

        for ($i = 0; $i < count($secondaryLineStationIds) - 1; $i++) {
            $this->addEdge(
                edges: $edges,
                edgeKeys: $edgeKeys,
                random: $random,
                from: $secondaryLineStationIds[$i],
                to: $secondaryLineStationIds[$i + 1],
                stationCoordinates: $stationCoordinates,
            );
        }

        $this->closeLoop(
            random: $random,
            lineStationIds: $mainLineStationIds,
            stationCoordinates: $stationCoordinates,
            edges: $edges,
            edgeKeys: $edgeKeys,
        );

        $this->closeLoop(
            random: $random,
            lineStationIds: $secondaryLineStationIds,
            stationCoordinates: $stationCoordinates,
            edges: $edges,
            edgeKeys: $edgeKeys,
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
        if ($targetCount < count($sharedHubIds) + 2) {
            $targetCount = count($sharedHubIds) + 2;
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

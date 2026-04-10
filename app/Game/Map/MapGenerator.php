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
    private const HIGH_SPEED_TOP_BOTTOM_POOL_SIZE = 10;
    private const HIGH_SPEED_MIN_SEGMENT_DISTANCE = 9;
    private const HIGH_SPEED_MIN_STATION_COUNT = 4;
    private const HIGH_SPEED_MAX_STATION_COUNT = 7;
    private const HIGH_SPEED_EXPRESS_DIVISOR = 12;
    private const HIGH_SPEED_X_VARIANCE = 1;
    private const HIGH_SPEED_Y_VARIANCE = 2;
    private const HIGH_SPEED_MAX_PERPENDICULAR_STEP = 7;
    private const HIGH_SPEED_INTERSECTION_CLEAR_RADIUS = 4;

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
                    from: $lineStationIds[$i],
                    to: $lineStationIds[$i + 1],
                    stationCoordinates: $stationCoordinates,
                );
            }

            $this->closeLoop(
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
                from: $bridge['from'],
                to: $bridge['to'],
                stationCoordinates: $stationCoordinates,
                avoidOverlaps: true,
            );

            if (! $added) {
                $this->addEdge(
                    edges: $edges,
                    edgeKeys: $edgeKeys,
                    from: $bridge['from'],
                    to: $bridge['to'],
                    stationCoordinates: $stationCoordinates,
                );
            }
        }

        $stationCounter = count($stationIds) + 1;
        if ($playerCount >= 5) {
            $this->addHighSpeedLine(
                random: $random,
                edges: $edges,
                edgeKeys: $edgeKeys,
                stationIds: $stationIds,
                stationCoordinates: $stationCoordinates,
                stationCounter: $stationCounter,
                forcedAxis: 'x',
                highSpeedLineId: 'HS1',
            );
            $this->addHighSpeedLine(
                random: $random,
                edges: $edges,
                edgeKeys: $edgeKeys,
                stationIds: $stationIds,
                stationCoordinates: $stationCoordinates,
                stationCounter: $stationCounter,
                forcedAxis: 'y',
                highSpeedLineId: 'HS2',
            );
        } else {
            $this->addHighSpeedLine(
                random: $random,
                edges: $edges,
                edgeKeys: $edgeKeys,
                stationIds: $stationIds,
                stationCoordinates: $stationCoordinates,
                stationCounter: $stationCounter,
                forcedAxis: null,
                highSpeedLineId: 'HS1',
            );
        }

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
     * @param array<string, true> $edgeKeys
     * @param list<string> $stationIds
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     */
    private function addHighSpeedLine(
        Randomizer $random,
        array &$edges,
        array &$edgeKeys,
        array &$stationIds,
        array &$stationCoordinates,
        int &$stationCounter,
        ?string $forcedAxis,
        string $highSpeedLineId,
    ): void {
        if (count($stationCoordinates) < 2) {
            return;
        }

        $existingStationIds = array_values(array_filter(
            array_keys($stationCoordinates),
            static fn (string $stationId): bool => ! str_starts_with(($stationCoordinates[$stationId]['line_id'] ?? ''), 'HS'),
        ));
        if (count($existingStationIds) < 2) {
            return;
        }

        $axis = $forcedAxis ?? $this->highSpeedAxis($stationCoordinates);
        $top = $existingStationIds;
        usort(
            $top,
            fn (string $left, string $right): int => $this->stationAxisValue($stationCoordinates[$left], $axis)
                <=> $this->stationAxisValue($stationCoordinates[$right], $axis),
        );
        $top = array_slice($top, 0, min(self::HIGH_SPEED_TOP_BOTTOM_POOL_SIZE, count($top)));
        if ($top === []) {
            return;
        }

        $bottom = $existingStationIds;
        usort(
            $bottom,
            fn (string $left, string $right): int => $this->stationAxisValue($stationCoordinates[$right], $axis)
                <=> $this->stationAxisValue($stationCoordinates[$left], $axis),
        );
        $bottom = array_slice($bottom, 0, min(self::HIGH_SPEED_TOP_BOTTOM_POOL_SIZE, count($bottom)));
        if ($bottom === []) {
            return;
        }

        for ($attempt = 0; $attempt < 30; $attempt++) {
            $from = $top[$random->getInt(0, count($top) - 1)];
            $candidateBottom = array_values(array_filter(
                $bottom,
                static fn (string $stationId): bool => $stationId !== $from,
            ));
            if ($candidateBottom === []) {
                continue;
            }

            $to = $candidateBottom[$random->getInt(0, count($candidateBottom) - 1)];
            $highSpeedCoordinates = $this->highSpeedStationCoordinates(
                random: $random,
                fromCoordinate: $stationCoordinates[$from],
                toCoordinate: $stationCoordinates[$to],
                existingCoordinates: $stationCoordinates,
                axis: $axis,
                highSpeedLineId: $highSpeedLineId,
            );

            if ($highSpeedCoordinates === []) {
                continue;
            }

            $route = [$from];
            foreach ($highSpeedCoordinates as $coordinate) {
                $stationId = sprintf('S%d', $stationCounter++);
                $stationIds[] = $stationId;
                $stationCoordinates[$stationId] = [
                    'x' => $coordinate['x'],
                    'y' => $coordinate['y'],
                    'line_id' => $highSpeedLineId,
                ];
                $route[] = $stationId;
            }
            $route[] = $to;

            for ($index = 0; $index < count($route) - 1; $index++) {
                $this->addExpressEdge(
                    edges: $edges,
                    edgeKeys: $edgeKeys,
                    from: $route[$index],
                    to: $route[$index + 1],
                    stationCoordinates: $stationCoordinates,
                );
            }

            return;
        }

        $fallbackFrom = $top[$random->getInt(0, count($top) - 1)];
        $fallbackBottom = array_values(array_filter(
            $bottom,
            static fn (string $stationId): bool => $stationId !== $fallbackFrom,
        ));
        if ($fallbackBottom === []) {
            return;
        }

        $fallbackTo = $fallbackBottom[$random->getInt(0, count($fallbackBottom) - 1)];
        $this->addExpressEdge(
            edges: $edges,
            edgeKeys: $edgeKeys,
            from: $fallbackFrom,
            to: $fallbackTo,
            stationCoordinates: $stationCoordinates,
        );
    }

    /**
     * @param array{x: int, y: int, line_id: string} $fromCoordinate
     * @param array{x: int, y: int, line_id: string} $toCoordinate
     * @param array<string, array{x: int, y: int, line_id: string}> $existingCoordinates
     * @return list<array{x: int, y: int}>
     */
    private function highSpeedStationCoordinates(
        Randomizer $random,
        array $fromCoordinate,
        array $toCoordinate,
        array $existingCoordinates,
        string $axis,
        string $highSpeedLineId,
    ): array {
        $dx = $toCoordinate['x'] - $fromCoordinate['x'];
        $dy = $toCoordinate['y'] - $fromCoordinate['y'];
        $distance = sqrt(($dx * $dx) + ($dy * $dy));
        if ($distance <= 0.001) {
            return [];
        }

        $stationCount = (int) floor($distance / self::HIGH_SPEED_MIN_SEGMENT_DISTANCE) + 1;
        $stationCount = max(self::HIGH_SPEED_MIN_STATION_COUNT, min(self::HIGH_SPEED_MAX_STATION_COUNT, $stationCount));
        $intermediateCount = max(1, $stationCount - 2);
        $proposed = [];
        $workingCoordinates = $existingCoordinates;
        $alongDelta = $axis === 'x' ? $dx : $dy;
        $directionAlong = $alongDelta === 0 ? 1 : ($alongDelta > 0 ? 1 : -1);

        for ($index = 1; $index <= $intermediateCount; $index++) {
            $ratio = $index / ($intermediateCount + 1);
            $baseX = (int) round($fromCoordinate['x'] + ($ratio * $dx));
            $baseY = (int) round($fromCoordinate['y'] + ($ratio * $dy));
            $placement = $this->placeHighSpeedStationCoordinate(
                random: $random,
                baseX: $baseX,
                baseY: $baseY,
                axis: $axis,
                directionAlong: $directionAlong,
                existingCoordinates: $workingCoordinates,
                previousCoordinate: $proposed[$index - 2] ?? $fromCoordinate,
            );
            if ($placement === null) {
                return [];
            }

            $proposed[] = $placement;
            $workingCoordinates[sprintf('__hs_%d', $index)] = [
                'x' => $placement['x'],
                'y' => $placement['y'],
                'line_id' => $highSpeedLineId,
            ];
        }

        return $proposed;
    }

    /**
     * @param array<string, array{x: int, y: int, line_id: string}> $existingCoordinates
     * @param array{x: int, y: int}|null $previousCoordinate
     * @return array{x: int, y: int}|null
     */
    private function placeHighSpeedStationCoordinate(
        Randomizer $random,
        int $baseX,
        int $baseY,
        string $axis,
        int $directionAlong,
        array $existingCoordinates,
        ?array $previousCoordinate,
    ): ?array {
        for ($attempt = 0; $attempt < 140; $attempt++) {
            $xVariance = $attempt < 90
                ? $random->getInt(-self::HIGH_SPEED_X_VARIANCE, self::HIGH_SPEED_X_VARIANCE)
                : $random->getInt(-3, 3);
            $yVariance = $attempt < 90
                ? $random->getInt(-self::HIGH_SPEED_Y_VARIANCE, self::HIGH_SPEED_Y_VARIANCE)
                : $random->getInt(-5, 5);
            $x = max(2, $baseX + $xVariance);
            $y = max(2, $baseY + $yVariance);

            if (! $this->isValidHighSpeedStep($previousCoordinate, $x, $y, $axis, $directionAlong)) {
                continue;
            }

            if (! $this->hasEnoughSpacing($x, $y, $existingCoordinates)) {
                continue;
            }

            return ['x' => $x, 'y' => $y];
        }

        return null;
    }

    /**
     * @param array{x: int, y: int}|null $previousCoordinate
     */
    private function isValidHighSpeedStep(
        ?array $previousCoordinate,
        int $x,
        int $y,
        string $axis,
        int $directionAlong,
    ): bool
    {
        if ($previousCoordinate === null) {
            return true;
        }

        $alongStep = $axis === 'x' ? ($x - $previousCoordinate['x']) : ($y - $previousCoordinate['y']);
        if (($alongStep * $directionAlong) < 1) {
            return false;
        }

        $perpendicularStep = $axis === 'x'
            ? abs($y - $previousCoordinate['y'])
            : abs($x - $previousCoordinate['x']);

        return $perpendicularStep <= self::HIGH_SPEED_MAX_PERPENDICULAR_STEP;
    }

    /**
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     */
    private function highSpeedAxis(array $stationCoordinates): string
    {
        $minX = null;
        $maxX = null;
        $minY = null;
        $maxY = null;

        foreach ($stationCoordinates as $coordinate) {
            $x = $coordinate['x'];
            $y = $coordinate['y'];
            $minX = $minX === null ? $x : min($minX, $x);
            $maxX = $maxX === null ? $x : max($maxX, $x);
            $minY = $minY === null ? $y : min($minY, $y);
            $maxY = $maxY === null ? $y : max($maxY, $y);
        }

        $spanX = ($maxX ?? 0) - ($minX ?? 0);
        $spanY = ($maxY ?? 0) - ($minY ?? 0);

        return $spanX >= $spanY ? 'x' : 'y';
    }

    /**
     * @param array{x: int, y: int, line_id: string} $coordinate
     */
    private function stationAxisValue(array $coordinate, string $axis): int
    {
        return $axis === 'x' ? $coordinate['x'] : $coordinate['y'];
    }

    /**
     * @param list<Edge> $edges
     * @param array<string, true> $edgeKeys
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     */
    private function addExpressEdge(
        array &$edges,
        array &$edgeKeys,
        string $from,
        string $to,
        array $stationCoordinates,
    ): bool {
        if ($from === $to) {
            return false;
        }

        $key = $this->edgeKey($from, $to);
        if (isset($edgeKeys[$key])) {
            return false;
        }

        $edges[] = new Edge(
            fromStationId: $from,
            toStationId: $to,
            travelTimeSeconds: $this->travelTimeSecondsBetween(
                stationCoordinates: $stationCoordinates,
                from: $from,
                to: $to,
                distanceDivisor: self::HIGH_SPEED_EXPRESS_DIVISOR,
            ),
            isExpress: true,
        );

        $edgeKeys[$key] = true;

        return true;
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

                    if ($this->shouldSkipIntersectionPromotion($first, $second)) {
                        continue;
                    }

                    $x = (int) round($intersection['x']);
                    $y = (int) round($intersection['y']);
                    $x = max(2, $x);
                    $y = max(2, $y);
                    $isHighSpeedCrossing = $first->isExpress xor $second->isExpress;
                    $involvesHighSpeed = $first->isExpress || $second->isExpress;
                    $stationId = $this->stationAtCoordinate($x, $y, $stationCoordinates);

                    if ($isHighSpeedCrossing) {
                        if ($stationId === null) {
                            $expressEdge = $first->isExpress ? $first : $second;
                            $expressLineId = $stationCoordinates[$expressEdge->fromStationId]['line_id'] ?? 'HS1';
                            $stationId = sprintf('S%d', $stationCounter++);
                            $stationIds[] = $stationId;
                            $stationCoordinates[$stationId] = ['x' => $x, 'y' => $y, 'line_id' => $expressLineId];
                            $intersectionStationIds[] = $stationId;
                        }
                    } else {
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
                    if ($involvesHighSpeed) {
                        $this->mergeStationsNearIntersection(
                            intersectionStationId: $stationId,
                            x: $x,
                            y: $y,
                            stationIds: $stationIds,
                            stationCoordinates: $stationCoordinates,
                            edges: $edges,
                            radius: self::HIGH_SPEED_INTERSECTION_CLEAR_RADIUS,
                        );
                    }
                    $splitApplied = true;
                    break 2;
                }
            }

            if (! $splitApplied) {
                return;
            }
        }
    }

    private function shouldSkipIntersectionPromotion(Edge $first, Edge $second): bool
    {
        return false;
    }

    /**
     * @param list<string> $stationIds
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     * @param list<Edge> $edges
     */
    private function mergeStationsNearIntersection(
        string $intersectionStationId,
        int $x,
        int $y,
        array &$stationIds,
        array &$stationCoordinates,
        array &$edges,
        int $radius,
    ): void {
        $toMerge = [];

        foreach ($stationCoordinates as $stationId => $coordinate) {
            if ($stationId === $intersectionStationId) {
                continue;
            }

            $distance = abs($coordinate['x'] - $x) + abs($coordinate['y'] - $y);
            if ($distance <= $radius) {
                $toMerge[$stationId] = true;
            }
        }

        if ($toMerge === []) {
            return;
        }

        $stationIds = array_values(array_filter(
            $stationIds,
            static fn (string $stationId): bool => ! isset($toMerge[$stationId]),
        ));

        foreach (array_keys($toMerge) as $stationId) {
            unset($stationCoordinates[$stationId]);
        }

        $merged = [];
        foreach ($edges as $edge) {
            $from = isset($toMerge[$edge->fromStationId]) ? $intersectionStationId : $edge->fromStationId;
            $to = isset($toMerge[$edge->toStationId]) ? $intersectionStationId : $edge->toStationId;
            if ($from === $to) {
                continue;
            }

            $distanceDivisor = $edge->isExpress ? self::HIGH_SPEED_EXPRESS_DIVISOR : 4;
            $candidate = new Edge(
                fromStationId: $from,
                toStationId: $to,
                travelTimeSeconds: $this->travelTimeSecondsBetween(
                    stationCoordinates: $stationCoordinates,
                    from: $from,
                    to: $to,
                    distanceDivisor: $distanceDivisor,
                ),
                isExpress: $edge->isExpress,
            );

            $key = $this->edgeKey($from, $to);
            $current = $merged[$key] ?? null;
            if ($current === null) {
                $merged[$key] = $candidate;
                continue;
            }

            if (! $current->isExpress && $candidate->isExpress) {
                $merged[$key] = $candidate;
                continue;
            }

            if (
                $current->isExpress === $candidate->isExpress
                && $candidate->travelTimeSeconds < $current->travelTimeSeconds
            ) {
                $merged[$key] = $candidate;
            }
        }

        $edges = array_values($merged);
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
                travelTimeSeconds: $this->travelTimeSecondsBetween(
                    stationCoordinates: $stationCoordinates,
                    from: $edge->fromStationId,
                    to: $stationId,
                ),
                isExpress: $edge->isExpress,
            ),
            new Edge(
                fromStationId: $stationId,
                toStationId: $edge->toStationId,
                travelTimeSeconds: $this->travelTimeSecondsBetween(
                    stationCoordinates: $stationCoordinates,
                    from: $stationId,
                    to: $edge->toStationId,
                ),
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
            travelTimeSeconds: $this->travelTimeSecondsBetween(
                stationCoordinates: $stationCoordinates,
                from: $from,
                to: $to,
            ),
            isExpress: false,
        );

        $edgeKeys[$key] = true;

        return true;
    }

    /**
     * @param array<string, array{x: int, y: int, line_id: string}> $stationCoordinates
     */
    private function travelTimeSecondsBetween(
        array $stationCoordinates,
        string $from,
        string $to,
        int $distanceDivisor = 4,
    ): int
    {
        $fromCoordinate = $stationCoordinates[$from] ?? null;
        $toCoordinate = $stationCoordinates[$to] ?? null;
        if ($fromCoordinate === null || $toCoordinate === null) {
            return 1;
        }

        $dx = $toCoordinate['x'] - $fromCoordinate['x'];
        $dy = $toCoordinate['y'] - $fromCoordinate['y'];
        $distance = sqrt(($dx * $dx) + ($dy * $dy));

        return max(1, (int) ceil($distance / max(1, $distanceDivisor)));
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

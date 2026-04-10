<?php

declare(strict_types=1);

namespace Tests\Game;

use App\Game\Map\MapGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MapGeneratorTest extends TestCase
{
    #[Test]
    public function generated_map_is_seeded_and_deterministic(): void
    {
        $generator = new MapGenerator();

        $mapA = $generator->generate(stationCount: 50, seed: 42);
        $mapB = $generator->generate(stationCount: 50, seed: 42);

        self::assertSame(array_keys($mapA->stations), array_keys($mapB->stations));
        self::assertSame($mapA->stationCoordinates, $mapB->stationCoordinates);

        $edgesA = array_map(
            static fn ($edge): string => sprintf('%s-%s-%d-%d', $edge->fromStationId, $edge->toStationId, $edge->travelTimeSeconds, (int) $edge->isExpress),
            $mapA->edges,
        );

        $edgesB = array_map(
            static fn ($edge): string => sprintf('%s-%s-%d-%d', $edge->fromStationId, $edge->toStationId, $edge->travelTimeSeconds, (int) $edge->isExpress),
            $mapB->edges,
        );

        self::assertSame($edgesA, $edgesB);
    }

    #[Test]
    public function generated_map_is_connected(): void
    {
        $generator = new MapGenerator();
        $map = $generator->generate(stationCount: 50, seed: 99);

        $visited = [];
        $queue = ['S1'];

        while ($queue !== []) {
            $current = array_shift($queue);

            if (isset($visited[$current])) {
                continue;
            }

            $visited[$current] = true;

            foreach ($map->edges as $edge) {
                if ($edge->fromStationId === $current && ! isset($visited[$edge->toStationId])) {
                    $queue[] = $edge->toStationId;
                }

                if ($edge->toStationId === $current && ! isset($visited[$edge->fromStationId])) {
                    $queue[] = $edge->fromStationId;
                }
            }
        }

        self::assertCount(count($map->stations), $visited);
    }

    #[Test]
    public function generated_map_has_no_hubs(): void
    {
        $generator = new MapGenerator();
        $map = $generator->generate(stationCount: 50, seed: 7);

        $hubCount = count(array_filter($map->stations, static fn ($station): bool => $station->isHub));

        self::assertSame(0, $hubCount);
    }

    #[Test]
    public function generated_map_assigns_grid_coordinates_with_minimum_spacing(): void
    {
        $generator = new MapGenerator();
        $map = $generator->generate(stationCount: 50, seed: 123);

        self::assertGreaterThanOrEqual(50, count($map->stationCoordinates));
        $lineIds = [];

        foreach ($map->stationCoordinates as $aId => $a) {
            self::assertGreaterThanOrEqual(0, $a['x']);
            self::assertGreaterThanOrEqual(0, $a['y']);
            self::assertLessThanOrEqual(100, $a['x']);
            self::assertLessThanOrEqual(100, $a['y']);
            self::assertMatchesRegularExpression('/^(L\d+|HS1)$/', $a['line_id']);
            $lineIds[$a['line_id']] = true;

            foreach ($map->stationCoordinates as $bId => $b) {
                if ($aId === $bId) {
                    continue;
                }

                $distance = abs($a['x'] - $b['x']) + abs($a['y'] - $b['y']);
                self::assertGreaterThanOrEqual(3, $distance);
            }
        }

        self::assertLessThanOrEqual(6, count($lineIds));
    }

    #[Test]
    public function generated_map_has_closed_loop_connection(): void
    {
        $generator = new MapGenerator();
        $map = $generator->generate(stationCount: 50, seed: 2026);

        // Connected graph + edges >= nodes guarantees at least one cycle.
        self::assertGreaterThanOrEqual(count($map->stations), count($map->edges));
    }

    #[Test]
    public function grid_size_scales_with_player_count(): void
    {
        $generator = new MapGenerator();
        $map = $generator->generate(stationCount: 50, seed: 17, playerCount: 4);

        foreach ($map->stationCoordinates as $coordinate) {
            self::assertLessThanOrEqual(120, $coordinate['x']);
            self::assertLessThanOrEqual(120, $coordinate['y']);
        }
    }

    #[Test]
    public function generated_map_builds_two_lines_by_default(): void
    {
        $generator = new MapGenerator();
        $map = $generator->generate(stationCount: 80, seed: 41026);

        $lineIds = [];

        foreach ($map->stationCoordinates as $coordinate) {
            $lineIds[$coordinate['line_id']] = true;
        }

        self::assertSame(3, count($lineIds));
        self::assertArrayHasKey('L1', $lineIds);
        self::assertArrayHasKey('L2', $lineIds);
        self::assertArrayHasKey('HS1', $lineIds);
    }

    #[Test]
    public function generated_map_builds_one_loop_per_player_up_to_six_players(): void
    {
        $generator = new MapGenerator();
        $map = $generator->generate(stationCount: 96, seed: 1104, playerCount: 6);

        $stationCountPerLine = [];
        foreach ($map->stationCoordinates as $coordinate) {
            $lineId = $coordinate['line_id'];
            $stationCountPerLine[$lineId] = ($stationCountPerLine[$lineId] ?? 0) + 1;
        }

        self::assertGreaterThanOrEqual(7, count($stationCountPerLine));

        for ($index = 1; $index <= 6; $index++) {
            $lineId = sprintf('L%d', $index);
            self::assertArrayHasKey($lineId, $stationCountPerLine);
            self::assertGreaterThanOrEqual(4, $stationCountPerLine[$lineId]);
        }
    }

    #[Test]
    public function generated_map_always_contains_one_high_speed_line(): void
    {
        $generator = new MapGenerator();
        $map = $generator->generate(stationCount: 80, seed: 2918, playerCount: 4);

        $expressEdges = array_filter(
            $map->edges,
            static fn ($edge): bool => $edge->isExpress,
        );

        self::assertNotEmpty($expressEdges);
    }

    #[Test]
    public function high_speed_line_connects_existing_network_and_hs_stations(): void
    {
        $generator = new MapGenerator();
        $map = $generator->generate(stationCount: 80, seed: 90211, playerCount: 4);

        $lineByStation = [];
        foreach ($map->stationCoordinates as $stationId => $coordinate) {
            $lineByStation[$stationId] = $coordinate['line_id'];
        }

        $expressEdges = array_values(array_filter(
            $map->edges,
            static fn ($edge): bool => $edge->isExpress,
        ));

        self::assertNotEmpty($expressEdges);

        $nonHsStationsOnExpress = [];
        foreach ($expressEdges as $edge) {
            $fromLine = $lineByStation[$edge->fromStationId] ?? null;
            $toLine = $lineByStation[$edge->toStationId] ?? null;

            if ($fromLine !== 'HS1') {
                $nonHsStationsOnExpress[$edge->fromStationId] = true;
            }
            if ($toLine !== 'HS1') {
                $nonHsStationsOnExpress[$edge->toStationId] = true;
            }
        }

        self::assertGreaterThanOrEqual(2, count($nonHsStationsOnExpress));
    }

    #[Test]
    public function five_or_more_players_generate_two_high_speed_axes(): void
    {
        $generator = new MapGenerator();
        $map = $generator->generate(stationCount: 96, seed: 9911, playerCount: 6);

        $hasHorizontalExpress = false;
        $hasVerticalExpress = false;

        foreach ($map->edges as $edge) {
            if (! $edge->isExpress) {
                continue;
            }

            $from = $map->stationCoordinates[$edge->fromStationId] ?? null;
            $to = $map->stationCoordinates[$edge->toStationId] ?? null;
            if ($from === null || $to === null) {
                continue;
            }

            $dx = abs($to['x'] - $from['x']);
            $dy = abs($to['y'] - $from['y']);
            if ($dx >= $dy) {
                $hasHorizontalExpress = true;
            }
            if ($dy >= $dx) {
                $hasVerticalExpress = true;
            }
        }

        self::assertTrue($hasHorizontalExpress);
        self::assertTrue($hasVerticalExpress);
    }
}

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
    public function generated_map_has_small_hub_count_between_two_and_four(): void
    {
        $generator = new MapGenerator();
        $map = $generator->generate(stationCount: 50, seed: 7);

        $hubCount = count(array_filter($map->stations, static fn ($station): bool => $station->isHub));

        self::assertGreaterThanOrEqual(2, $hubCount);
        self::assertLessThanOrEqual(4, $hubCount);
    }

    #[Test]
    public function generated_map_assigns_grid_coordinates_with_minimum_spacing(): void
    {
        $generator = new MapGenerator();
        $map = $generator->generate(stationCount: 50, seed: 123);

        self::assertCount(50, $map->stationCoordinates);

        foreach ($map->stationCoordinates as $aId => $a) {
            self::assertGreaterThanOrEqual(0, $a['x']);
            self::assertGreaterThanOrEqual(0, $a['y']);
            self::assertLessThanOrEqual(100, $a['x']);
            self::assertLessThanOrEqual(100, $a['y']);
            self::assertSame('L1', $a['line_id']);

            foreach ($map->stationCoordinates as $bId => $b) {
                if ($aId === $bId) {
                    continue;
                }

                $distance = abs($a['x'] - $b['x']) + abs($a['y'] - $b['y']);
                self::assertGreaterThanOrEqual(2, $distance);
            }
        }
    }

    #[Test]
    public function generated_map_has_closed_loop_connection(): void
    {
        $generator = new MapGenerator();
        $map = $generator->generate(stationCount: 50, seed: 2026);

        self::assertCount(50, $map->edges);

        $closingEdge = $map->edges[array_key_last($map->edges)];

        self::assertSame('S50', $closingEdge->fromStationId);
        self::assertSame('S1', $closingEdge->toStationId);
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
}

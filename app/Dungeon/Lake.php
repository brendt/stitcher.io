<?php

namespace App\Dungeon;

use Generator;

final class Lake
{
    public ?Point $relic = null;

    public function __construct(
        public Point $origin,
        /** @var \App\Dungeon\LakePoint[][] */
        public array $lakePoints = [],
        /** @var \App\Dungeon\Point[][] */
        public array $edges = [],
        public bool $isDiscovered = false,
    ) {}

    public function addEdge(Point $edge): void
    {
        $this->edges[$edge->x][$edge->y] = $edge;
    }

    public function hasEdge(Point $edge): bool
    {
        return isset($this->edges[$edge->x][$edge->y]);
    }

    /** @return Generator<Point> */
    public function loopEdges(): Generator
    {
        foreach ($this->edges as $row) {
            foreach ($row as $edge) {
                yield $edge;
            }
        }
    }

    public function addLakePoint(LakePoint $point): void
    {
        $this->lakePoints[$point->point->x][$point->point->y] = $point;
    }

    public function hasLakePoint(Point $point): bool
    {
        return isset($this->lakePoints[$point->x][$point->y]);
    }

    public function getLakePoint(Point $point): ?LakePoint
    {
        return $this->lakePoints[$point->x][$point->y] ?? null;
    }

    /** @return Generator<\App\Dungeon\LakePoint> */
    public function loopLakePoints(): Generator
    {
        foreach ($this->lakePoints as $row) {
            foreach ($row as $lakePoint) {
                yield $lakePoint;
            }
        }
    }

    public static function randomShape(Level $level): array
    {
        $shapes = self::shapes($level);

        if ($shapes === []) {
            return [];
        }

        $shape = $shapes[array_rand($shapes)];

        $rows = explode(PHP_EOL, $shape);

        return array_map(fn($row) => str_split($row), $rows);
    }

    public static function shapes(Level $level): array
    {
        return match ($level) {
            Level::NOOB, Level::NOVICE => [],
            Level::MASTER => [
" 0000000
001111100
012222210
012333210
012333210
012333210
012222210
001111100
 0000000",
"000000000000
001111100000
012222110000
012333210000
012222221100
001112222110
000011112110
000000011100
000000000000"
            ],
            Level::GRANDMASTER => [
"   0000000
0001111111000
0112222221100
0112333321110
0112222221110
0001111221000
     00122211000
     0112332100
     01123321100
     0112222100
     00011111000
        0000000",
"000000000000000
000111111100000
001222222110000
001233332210000
001222222210000
001122222221000
000011122221100
000001112332100
000001112222100
000000011111000
000000000000000"
            ],
            Level::LEGENDARY => [
"   0000000
0001111111000
0112222221100
0112333321110
0112222221110
0001111221000
     00122211000
     0112332100
     01123321100
     0112222100
     00011111000
        0000000",
"     0000000000
     0111111110
  01122222222110
  0112333333332110
 011223333333332210
01122333333333332210
011233333333333332110
01122333333333332210
 01122222222222221110
   011111222223322110
    01111123333332110
    01112223333332110
    01112223333332110
      01122222222110
       01111111110
       0000000000",
            ],
        };
    }
}
<?php

namespace App\Map\Layer;


use App\Map\Tile\GenericTile\BaseTile;
use Generator;

final class BaseLayer
{
    /** @var \App\Map\Layer\Layer[] */
    private array $pendingLayers = [];

    /** @var \App\Map\Tile\Tile[][] */
    private array $board = [];

    public function __construct(
        public readonly int $width,
        public readonly int $height,
    ) {}

    public function generate(): self
    {
        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                $tile = $this->board[$x][$y] ?? new BaseTile($x, $y);

                foreach ($this->pendingLayers as $layer) {
                    $tile = $layer->generate($tile, $this);
                }

                $this->board[$x][$y] = $tile;
            }
        }

        $this->pendingLayers = [];

        return $this;
    }

    /**
     * @return \App\Map\Tile\Tile[][]
     */
    public function getBoard(): array
    {
        return $this->board;
    }

    public function get(int $x, int $y): ?object
    {
        return $this->board[$x][$y] ?? null;
    }

    public function add(Layer $layer): self
    {
        $this->pendingLayers[] = $layer;

        return $this;
    }

    public function loop(): Generator
    {
        foreach ($this->board as $row) {
            foreach ($row as $tile) {
                yield $tile;
            }
        }
    }
}

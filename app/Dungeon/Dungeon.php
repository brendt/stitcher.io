<?php

namespace App\Dungeon;

use App\Dungeon\Entities\Tile;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Events\TileGenerated;
use function Tempest\EventBus\event;
use function Tempest\Mapper\map;
use function Tempest\Support\arr;

final class Dungeon
{
    private(set) int $version = 0;
    private(set) array $changes = [];
    private(set) array $tiles = [];
    private(set) Point $playerPosition;
    private(set) bool $hasEnded = false;

    public Tile $currentTile {
        get => $this->getTile($this->playerPosition);
    }

    public function __construct()
    {
        $this->playerPosition = new Point(0, 0);
        $this->addTile(new Tile(clone $this->playerPosition));
        event(new TileGenerated($this->currentTile));
    }

    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'playerPosition' => $this->playerPosition,
            'tiles' => $this->tiles,
            'hasEnded' => $this->hasEnded,
        ];
    }

    public static function fromArray(array $data): self
    {
        return map($data)->to(self::class);
    }

    public function move(Direction $direction): void
    {
        if (! $this->currentTile->canMoveTo($direction)) {
            return;
        }

        $neighbourPosition = $this->playerPosition->getNeighbour($direction);

        $neighbourTile = $this->getTile($neighbourPosition);

        if ($neighbourTile && ! $neighbourTile->canMoveTo($direction->opposite())) {
            return;
        }

        $oldPosition = $this->playerPosition;
        $this->playerPosition = $neighbourPosition;

        if (! $neighbourTile) {
            $this->generateTile($oldPosition, $this->playerPosition);
        }

        event(new PlayerMoved($oldPosition, $this->playerPosition));
    }

    public function consumeChanges(): array
    {
        $changes = $this->changes;

        $this->changes = [];

        return $changes;
    }

    public function addChange(string $name, array $payload): self
    {
        $this->changes[] = [
            'name' => $name,
            'payload' => $payload,
        ];

        return $this;
    }

    public function addTile(Tile $tile): self
    {
        $this->tiles[$tile->point->x][$tile->point->y] = $tile;

        return $this;
    }

    private function generateTile(Point $from, Point $to): void
    {
        if ($this->getTile($to)) {
            return;
        }

        $minimumDirectionCount = match (true) {
            $to->x < 2 && $to->y < 2 => 3,
            $to->x < 6 && $to->y < 6 => 2,
            default => 1,
        };

        $allowedDirections = array_rand(Direction::cases(), rand($minimumDirectionCount, 4));

        $directions = arr(Direction::cases())
            ->filter(fn (Direction $direction, int $index) => in_array($index, $allowedDirections, strict: true))
            ->add($to->directionTo($from))
            ->unique()
            ->values()
            ->toArray();

        $tile = new Tile($to, directions: $directions);
        $this->addTile($tile);

        event(new TileGenerated($tile));
    }

    public function getTile(Point $point): ?Tile
    {
        return $this->tiles[$point->x][$point->y] ?? null;
    }
}
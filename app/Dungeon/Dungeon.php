<?php

namespace App\Dungeon;

use App\Dungeon\Entities\Tile;
use App\Dungeon\Events\TileGenerated;
use Generator;
use function Tempest\EventBus\event;
use function Tempest\Mapper\map;
use function Tempest\Support\arr;

final class Dungeon
{
    use DungeonActions;

    private(set) int $version = 0;
    private(set) array $changes = [];
    private(set) array $tiles = [];
    private(set) Point $playerPosition;
    private(set) bool $hasEnded = false;
    private(set) int $coins = 0;
    private(set) int $health = 100;
    private(set) int $maxHealth = 100;
    private(set) int $mana = 0;
    private(set) int $maxMana = 150;

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
            'coins' => $this->coins,
            'mana' => $this->mana,
            'maxMana' => $this->maxMana,
            'health' => $this->health,
            'maxHealth' => $this->maxHealth,
        ];
    }

    public static function fromArray(array $data): self
    {
        return map($data)->to(self::class);
    }

    public function consumeChanges(): array
    {
        $this->version++;

        $changes = $this->changes;

        $this->changes = [];

        return $changes;
    }

    public function registerChange(string $name, array $payload): self
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

    public function generateTile(Point $from, Point $to): void
    {
        if ($this->getTile($to)) {
            return;
        }

        $absX = abs($to->x);
        $absY = abs($to->y);

        $minimumDirectionCount = match (true) {
            $absX < 1 && $absY < 1 => 3,
            $absX < 6 && $absY < 6 => 2,
            default => 1,
        };

        $allowedDirections = array_rand(Direction::cases(), rand($minimumDirectionCount, 4));

        if (! is_array($allowedDirections)) {
            $allowedDirections = [$allowedDirections];
        }

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

    /** @return Generator<Tile> */
    public function loopTiles(): Generator
    {
        foreach ($this->tiles as $row) {
            foreach ($row as $tile) {
                yield $tile;
            }
        }
    }
}
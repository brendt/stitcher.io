<?php

namespace App\Dungeon;

use App\Dungeon\Events\TileGenerated;
use Generator;
use ReflectionClass;
use function Tempest\EventBus\event;
use function Tempest\Support\arr;

final class Dungeon
{
    use DungeonActions;

    public int $version = 0;
    public array $changes = [];
    public array $tiles = [];
    public ?Point $playerPosition = null;
    public bool $hasEnded = false;
    public int $coins = 0;
    public int $health = 100;
    public int $maxHealth = 100;
    public int $mana = 0;
    public int $maxMana = 150;
    public int $stability = 100;
    public int $maxStability = 100;
    public int $maxHandCount = 5;

    /** @var \App\Dungeon\Card[] $hand */
    public array $hand = [];

    /** @var \App\Dungeon\Card[] $deck */
    public array $deck = [];

    /** @var \App\Dungeon\Card[] */
    public array $permanentCards = [];

    public ?Card $activeCard = null;

    public ?Card $passiveCard = null;

    /** @var \App\Dungeon\Dweller[] */
    public array $dwellers = [];

    public int $visibilityRadius = 5;

    public Tile $currentTile {
        get => $this->getTile($this->playerPosition);
    }

    /** @param \App\Dungeon\Card[] $deck */
    public static function new(array $deck): self
    {
        $self = new self();

        $self->playerPosition = new Point(0, 0);
        $self->addTile(new Tile(clone $self->playerPosition));

        foreach ($deck as $card) {
            $self->addToDeck($card);
        }

        for ($i = 0; $i < $self->maxHandCount; $i++) {
            $self->drawCard();
        }

        $self->spawnDweller();

        return $self;
    }

    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'playerPosition' => $this->playerPosition,
            'tiles' => arr($this->tiles)->map(fn (array $tiles) => arr($tiles)->map(fn (Tile $tile) => $tile->toArray())->toArray())->toArray(),
            'dwellers' => arr($this->dwellers)->map(fn (array $dwellers) => arr($dwellers)->map(fn (Dweller $dweller) => $dweller->toArray())->toArray())->toArray(),
            'hasEnded' => $this->hasEnded,
            'coins' => $this->coins,
            'mana' => $this->mana,
            'maxMana' => $this->maxMana,
            'health' => $this->health,
            'maxHealth' => $this->maxHealth,
            'stability' => $this->stability,
            'maxStability' => $this->maxStability,
            'hand' => arr($this->hand)->map(fn (Card $card) => $card->toArray())->toArray(),
            'deck' => arr($this->deck)->map(fn (Card $card) => $card->toArray())->toArray(),
            'permanentCards' => arr($this->permanentCards)->map(fn (Card $card) => $card->toArray())->toArray(),
            'passiveCard' => $this->passiveCard?->toArray(),
            'activeCard' => $this->activeCard?->toArray(),
            'visibilityRadius' => $this->visibilityRadius,
        ];
    }

    public static function fromArray(array $data): self
    {
        $data['hand'] = arr($data['hand'])->map(fn (array $card) => $card['class']::fromArray($card))->toArray();
        $data['deck'] = arr($data['deck'])->map(fn (array $card) => $card['class']::fromArray($card))->toArray();
        $data['permanentCards'] = arr($data['permanentCards'])->map(fn (array $card) => $card['class']::fromArray($card))->toArray();
        $data['passiveCard'] = $data['passiveCard'] ? $data['passiveCard']['class']::fromArray($data['passiveCard']) : null;
        $data['activeCard'] = $data['activeCard'] ? $data['activeCard']['class']::fromArray($data['activeCard']) : null;

        foreach ($data['tiles'] as $x => $row) {
            foreach ($row as $y => $tile) {
                $data['tiles'][$x][$y] = Tile::fromArray($tile);
            }
        }

        foreach ($data['dwellers'] as $x => $row) {
            foreach ($row as $y => $dweller) {
                $data['dwellers'][$x][$y] = Dweller::fromArray($dweller);
            }
        }

        $self = new ReflectionClass(self::class)->newInstanceWithoutConstructor();

        foreach ($data as $key => $value) {
            $self->{$key} = $value;
        }

        return $self;
    }

    public function consumeChanges(): array
    {
        if ($this->changes === []) {
            return [];
        }

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

    public function addToDeck(Card $card): self
    {
        $this->deck[$card->id] = $card;

        return $this;
    }

    public function generateTile(Point $from, Point $to): void
    {
        if ($this->tryTile($to)) {
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

    public function getTile(Point $point): Tile
    {
        return $this->tiles[$point->x][$point->y];
    }

    public function tryTile(Point $point): ?Tile
    {
        return $this->tiles[$point->x][$point->y] ?? null;
    }

    public function hasTile(Point $point): bool
    {
        return isset($this->tiles[$point->x][$point->y]);
    }

    public function withinVisibilityRange(Point $point): bool
    {
        if ($this->playerPosition === null) {
            return false;
        }

        $dx = $point->x - $this->playerPosition->x;
        $dy = $point->y - $this->playerPosition->y;
        $distance = hypot($dx, $dy);

        return $distance < $this->visibilityRadius;
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

    /** @return Generator<\App\Dungeon\Dweller> */
    public function loopDwellers(): Generator
    {
        foreach ($this->dwellers as $row) {
            foreach ($row as $dweller) {
                yield $dweller;
            }
        }
    }

    public function tileCount(): int
    {
        return iterator_count($this->loopTiles());
    }
}
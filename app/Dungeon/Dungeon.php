<?php

namespace App\Dungeon;

use App\Dungeon\Persistence\DungeonUserCard;
use App\Dungeon\Persistence\DungeonUserStats;
use App\Dungeon\Repositories\DeckRepository;
use App\Dungeon\Repositories\StatsRepository;
use App\Support\Authentication\User;
use Generator;
use function Tempest\Support\arr;

final class Dungeon
{
    public const int CURRENT_CAMPAIGN = 2;
    public const int MAX_HAND_COUNT = 20;
    public const string HAS_SEEN_SHARD_SHOP = 'has_seen_shard_shop';
    public const string NICKNAME = 'nickname';

    use DungeonActions;

    public bool $cheat = false;
    private StatsRepository $statsRepository;
    private DeckRepository $deckRepository;
    public array $changes = [];
    public User $user;
    public int $version = 0;
    public ?Point $playerPosition = null;
    public bool $hasEnded = false;
    public int $coins = 0;
    public int $victoryPoints = 0;
    public int $shards = 0;
    public int $health = 100;
    public int $maxHealth = 100;
    public int $mana = 0;
    public int $maxMana = 150;
    public int $stability = 100;
    public int $maxStability = 100;
    public int $maxHandCount = 5;
    public int $experience = 0;
    private DungeonUserStats $stats;
    private Level $level;
    public int $visibilityRadius = 5;
    public ?Card $activeCard = null;
    public ?Card $passiveCard = null;

    /** @var \App\Dungeon\Tile[][] */
    public array $tiles = [];

    /** @var \App\Dungeon\Card[] $hand */
    public array $hand = [];

    /** @var \App\Dungeon\Card[] $deck */
    public array $deck = [];

    /** @var \App\Dungeon\Card[] */
    public array $permanentCards = [];

    /** @var \App\Dungeon\Dweller[][] */
    public array $dwellers = [];

    public Tile $currentTile {
        get => $this->getTile($this->playerPosition);
    }

    public ?Point $artifactLocation = null;

    /** @var \App\Dungeon\Point[][] */
    public array $healthAltars = [];

    /** @var \App\Dungeon\Point[][] */
    public array $manaAltars = [];

    /** @var \App\Dungeon\Point[][] */
    public array $stabilityAltars = [];

    /** @var \App\Dungeon\Point[][] */
    public array $victoryPointLocations = [];

    /** @var \App\Dungeon\Point[][] */
    public array $shardLocations = [];

    public static function new(
        User $user,
        DeckRepository $deckRepository,
        StatsRepository $statsRepository,
        ?array $deck = null,
    ): self {
        $self = new self();
        $self->user = $user;
        $self->deckRepository = $deckRepository;
        $self->statsRepository = $statsRepository;
        $self->stats = $statsRepository->forUser($user);
        $self->level = $self->stats->level;

        $self->playerPosition = new Point(0, 0);
        $self->addTile(new Tile(clone $self->playerPosition, isOrigin: true));

        $deck ??= $self->deckRepository->activeCardsForUser($user);

        foreach ($deck as $card) {
            if ($card instanceof DungeonUserCard) {
                $card = $card->card;
            }

            $self->addToDeck($card);
        }

        for ($i = 0; $i < $self->maxHandCount; $i++) {
            $self->drawCard();
        }

        for ($i = 0; $i < $self->level->initialDwellerCount(); $i++) {
            $self->spawnDweller();
        }

        $self->spawnArtifact();

        for ($i = 0; $i < $self->level->altarCount(); $i++) {
            $self->spawnHealthAltar();
            $self->spawnManaAltar();
            $self->spawnStabilityAltar();
        }

        return $self;
    }

    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'playerPosition' => $this->playerPosition,
            'artifactLocation' => $this->artifactLocation,
            'tiles' => arr($this->tiles)->map(fn (array $tiles) => arr($tiles)->map(fn (Tile $tile) => $tile->toArray())->toArray())->toArray(),
            'dwellers' => arr($this->dwellers)->map(fn (array $dwellers) => arr($dwellers)->map(fn (Dweller $dweller) => $dweller->toArray())->toArray())->toArray(),
            'hasEnded' => $this->hasEnded,
            'coins' => $this->coins,
            'victoryPoints' => $this->victoryPoints,
            'shards' => $this->shards,
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
            'healthAltars' => $this->healthAltars,
            'manaAltars' => $this->manaAltars,
            'stabilityAltars' => $this->stabilityAltars,
            'experience' => $this->experience,
        ];
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

    public function withinVisibilityRadius(Point $point): bool
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

    /** @return Generator<\App\Dungeon\Point> */
    public function loopHealthAltar(): Generator
    {
        foreach ($this->healthAltars as $row) {
            foreach ($row as $altar) {
                yield $altar;
            }
        }
    }

    /** @return Generator<\App\Dungeon\Point> */
    public function loopStabilityAltar(): Generator
    {
        foreach ($this->stabilityAltars as $row) {
            foreach ($row as $altar) {
                yield $altar;
            }
        }
    }

    /** @return Generator<\App\Dungeon\Point> */
    public function loopManaAltar(): Generator
    {
        foreach ($this->manaAltars as $row) {
            foreach ($row as $altar) {
                yield $altar;
            }
        }
    }

    public function loopVisibleDwellers(): Generator
    {
        foreach ($this->loopDwellers() as $dweller) {
            if ($this->withinVisibilityRadius($dweller->point)) {
                yield $dweller;
            }
        }
    }

    public function tileCount(): int
    {
        return iterator_count($this->loopTiles());
    }

    public function getDweller(Point $point): ?Dweller
    {
        return $this->dwellers[$point->x][$point->y] ?? null;
    }
}
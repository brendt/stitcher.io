<?php

namespace App\Dungeon;

use function Tempest\Mapper\map;
use function Tempest\Support\arr;

final class Tile
{
    public function __construct(
        /** @var \App\Dungeon\Direction[] $directions */
        public readonly Point $point,
        public readonly string $color = 'lightgray',
        public array $directions = [Direction::TOP, Direction::LEFT, Direction::RIGHT, Direction::BOTTOM],
        public bool $isOrigin = false,
        public bool $isActive = false,
        public bool $isCollapsed = false,
        public bool $isTrapped = false,
        public bool $isManaAltar = false,
        public bool $isHealthAltar = false,
        public bool $isStabilityAltar = false,
        public bool $isArtifact = false,
        public bool $isShard = false,
        public bool $isShardCollected = false,
        public bool $isVictoryPoint = false,
        public bool $isVictoryPointCollected = false,
        public bool $isShop = false,
        public int $altarCooldown = 0,
        public int $coins = 0,
        public bool $isSupported = false,
    ) {}

    public function toArray(): array
    {
        return [
            'point' => $this->point,
            'color' => $this->color,
            'directions' => arr($this->directions)->map(fn (Direction $direction) => $direction->value)->toArray(),
            'isOrigin' => $this->isOrigin,
            'isActive' => $this->isActive,
            'isCollapsed' => $this->isCollapsed,
            'isTrapped' => $this->isTrapped,
            'isManaAltar' => $this->isManaAltar,
            'isHealthAltar' => $this->isHealthAltar,
            'isStabilityAltar' => $this->isStabilityAltar,
            'isArtifact' => $this->isArtifact,
            'isShard' => $this->isShard,
            'isShardCollected' => $this->isShardCollected,
            'isVictoryPoint' => $this->isVictoryPoint,
            'isVictoryPointCollected' => $this->isVictoryPointCollected,
            'isShop' => $this->isShop,
            'altarCooldown' => $this->altarCooldown,
            'coins' => $this->coins,
            'isSupported' => $this->isSupported,
        ];
    }

    public static function initial(): self
    {
        $point = new Point(rand(1, 3), rand(1, 3));

        $directions = [
            Direction::RIGHT,
            Direction::BOTTOM,
        ];

        if ($point->x > 1) {
            $directions[] = Direction::LEFT;
        }

        if ($point->y > 1) {
            $directions[] = Direction::TOP;
        }

        return new self(
            point: $point,
            color: 'skyblue',
            directions: $directions,
            isOrigin: true,
            isActive: true,
        );
    }

    public function is(self $other): bool
    {
        return $this->point->equals($other->point);
    }

    public function canMoveTo(Direction $direction): bool
    {
        return in_array($direction, $this->directions);
    }

    public function isAltar(): bool
    {
        return $this->isHealthAltar
            || $this->isStabilityAltar
            || $this->isManaAltar;
    }

    public function canCollapse(): bool
    {
        return ! (
            $this->isOrigin
            || $this->isAltar()
            || $this->isCollapsed
            || $this->isTrapped
            || $this->isArtifact
            || $this->isShard
            || $this->isShop
            || $this->isSupported
        );
    }

    public function hasBorder(Direction $direction): bool
    {
        return ! in_array($direction, $this->directions);
    }
}

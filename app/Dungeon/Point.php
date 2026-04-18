<?php

namespace App\Dungeon;

final readonly class Point
{
    public function __construct(
        public int $x,
        public int $y,
    ) {}

    public static function fromString(string $string): self
    {
        return new self(...explode(',', $string));
    }

    public function __toString(): string
    {
        return "{$this->x},{$this->y}";
    }

    public function equals(self $other): bool
    {
        return $this->x === $other->x
            && $this->y === $other->y;
    }

    public function distanceTo(Point $other): float
    {
        return round(abs(
            sqrt(
                (($other->x - $this->x) ** 2)
                + (($other->y - $this->y) ** 2),
            ),
        ), 2);
    }

    public function relativeDirectionTo(Point $other): string
    {
        $xDifference = $other->x - $this->x;
        $yDifference = $other->y - $this->y;

        return match (true) {
            $xDifference < 0 && $yDifference < 0 => 'NW',
            $xDifference < 0 && $yDifference > 0 => 'SW',
            $xDifference > 0 && $yDifference < 0 => 'NE',
            $xDifference > 0 && $yDifference > 0 => 'SE',
            $xDifference < 0 && $yDifference === 0 => 'W',
            $xDifference > 0 && $yDifference === 0 => 'E',
            $xDifference === 0 && $yDifference > 0 => 'S',
            $xDifference === 0 && $yDifference < 0 => 'N',
        };
    }

    public function move(?int $x = null, ?int $y = null): self
    {
        return new self($x ?? $this->x, $y ?? $this->y);
    }

    public function translate(int $x = 0, int $y = 0): self
    {
        return new self(
            $this->x + $x,
            $this->y + $y,
        );
    }

    public function getNeighbour(Direction $direction): Point
    {
        return match ($direction) {
            Direction::TOP => $this->translate(y: -1),
            Direction::LEFT => $this->translate(x: -1),
            Direction::RIGHT => $this->translate(x: 1),
            Direction::BOTTOM => $this->translate(y: 1),
        };
    }

    public function directionTo(Point $other): Direction
    {
        return match (true) {
            $this->x < $other->x => Direction::RIGHT,
            $this->x > $other->x => Direction::LEFT,
            $this->y < $other->y => Direction::BOTTOM,
            $this->y > $other->y => Direction::TOP,
        };
    }

    public function rebase(Point $center, int $radius): self
    {
        return new self(
            x: $this->x - $center->x + $radius,
            y: $this->y - $center->y + $radius,
        );
    }

    public function toArray(): array
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
        ];
    }
}

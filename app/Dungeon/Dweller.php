<?php

namespace App\Dungeon;

final class Dweller
{
    public function __construct(
        public Point $point,
        public bool $alwaysVisible = false,
    ) {}

    public function toArray(): array
    {
        return [
            'point' => $this->point,
            'alwaysVisible' => $this->alwaysVisible,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}

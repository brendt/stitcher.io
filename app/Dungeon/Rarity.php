<?php

namespace App\Dungeon;

enum Rarity
{
    case COMMON;
    case RARE;
    case EPIC;
    case META;

    public function isCommon(): bool
    {
        return $this === self::COMMON;
    }

    public function isRare(): bool
    {
        return $this === self::RARE;
    }

    public function isEpic(): bool
    {
        return $this === self::EPIC;
    }

    public function isMeta(): bool
    {
        return $this === self::META;
    }

    public function getChance(): int
    {
        return match($this) {
            self::COMMON => 80,
            self::RARE => 40,
            self::EPIC => 10,
            self::META => 20,
        };
    }
}

<?php

namespace App\Dungeon;

enum Level: int
{
    case NOOB = 0;
    case NOVICE = 1000;
    case MASTER = 10_000;
    case GRANDMASTER = 25_000;
    case LEGENDARY = 100_000;

    public function getName(): string
    {
        return match($this) {
            self::NOOB => 'Noob',
            self::NOVICE => 'Novice',
            self::MASTER => 'Master',
            self::GRANDMASTER => 'Grandmaster',
            self::LEGENDARY => 'Legendary',
        };
    }

    public function nextLevel(): ?self
    {
        return match ($this) {
            self::NOOB => self::NOVICE,
            self::NOVICE => self::MASTER,
            self::MASTER => self::GRANDMASTER,
            self::GRANDMASTER => self::LEGENDARY,
            self::LEGENDARY => null,
        };
    }

    public static function forExperience(int $experience): self
    {
        foreach(array_reverse(self::cases()) as $level) {
            if ($experience >= $level->value) {
                return $level;
            }
        }

        return self::NOOB;
    }

    public function hasAccessTo(Level $other): bool
    {
        return $this->value >= $other->value;
    }

    public function freeTokenAmount(): int
    {
        return match ($this) {
            self::NOOB => 20,
            self::NOVICE => 15,
            self::MASTER => 10,
            self::GRANDMASTER, self::LEGENDARY => 5,
        };
    }
}

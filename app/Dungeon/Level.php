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
        return match ($this) {
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
        foreach (array_reverse(self::cases()) as $level) {
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

    public function priceModifier(): float
    {
        return match ($this) {
            self::NOOB => 0.5,
            self::NOVICE => 1.0,
            self::MASTER => 1.5,
            self::GRANDMASTER => 2.0,
            self::LEGENDARY => 2.5,
        };
    }

    public function shouldSpawnTrap(): bool
    {
        return match ($this) {
            self::NOOB => random_int(1, 200) === 1,
            self::NOVICE => random_int(1, 150) === 1,
            self::MASTER => random_int(1, 100) === 1,
            self::GRANDMASTER, self::LEGENDARY => random_int(1, 75) === 1,
        };
    }

    public function initialDwellerCount(): int
    {
        return match ($this) {
            self::NOOB => 1,
            self::NOVICE => random_int(1, 3),
            self::MASTER => random_int(3, 5),
            self::GRANDMASTER => random_int(4, 6),
            self::LEGENDARY => random_int(5, 8),
        };
    }

    public function maxArtifactDistance(): int
    {
        return match ($this) {
            self::NOOB => 20,
            self::NOVICE => 30,
            self::MASTER => 40,
            self::GRANDMASTER => 50,
            self::LEGENDARY => 60,
        };
    }

    public function maxAltarDistance(): int
    {
        return match ($this) {
            self::LEGENDARY => 50,
            self::GRANDMASTER, self::MASTER => 40,
            default => 30,
        };
    }

    public function minAltarDistance(): int
    {
        return match ($this) {
            self::LEGENDARY => 25,
            self::GRANDMASTER, self::MASTER => 15,
            default => 10,
        };
    }

    public function altarCount(): int {
        return match ($this) {
            self::NOOB => 0,
            self::NOVICE => 1,
            self::MASTER => 2,
            self::GRANDMASTER => 2,
            self::LEGENDARY => 3,
        };
    }

    public function maxTreasureDistance(): int
    {
        return match ($this) {
            self::LEGENDARY => 60,
            self::GRANDMASTER, self::MASTER => 50,
            default => 40,
        };
    }

    public function minTreasureDistance(): int
    {
        return match ($this) {
            self::LEGENDARY => 25,
            self::GRANDMASTER, self::MASTER => 15,
            default => 10,
        };
    }

    public function maxDwellerDistance(): int
    {
        return match ($this) {
            self::LEGENDARY => 60,
            self::GRANDMASTER, self::MASTER => 50,
            default => 40,
        };
    }

    public function nextMilestone(): string
    {
        $xp = $this->nextLevel()->value;

        if ($xp <= 1000) {
            return $xp;
        }

        return (int)round($xp / 1000) . 'k';
    }

    public function maxCoinCount(): int
    {
        return match ($this) {
            self::NOOB => 20,
            self::NOVICE => 40,
            self::MASTER => 50,
            self::GRANDMASTER => 75,
            self::LEGENDARY => 100,
        };
    }
}

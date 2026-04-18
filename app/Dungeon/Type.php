<?php

namespace App\Dungeon;

enum Type: string
{
    case ACTIVE = 'active';
    case PASSIVE = 'passive';
    case IMMEDIATE = 'immediate';
    case PERMANENT = 'permanent';
    case META = 'meta';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isPassive(): bool
    {
        return $this === self::PASSIVE;
    }

    public function isImmediate(): bool
    {
        return $this === self::IMMEDIATE;
    }

    public function isPermanent(): bool
    {
        return $this === self::PERMANENT;
    }

    public function isMeta(): bool
    {
        return $this === self::META;
    }
}

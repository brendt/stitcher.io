<?php

namespace App\Dungeon;

enum Direction: string
{
    case TOP = 'top';
    case LEFT = 'left';
    case RIGHT = 'right';
    case BOTTOM = 'bottom';

    public function opposite(): self
    {
        return match($this) {
            self::TOP => self::BOTTOM,
            self::BOTTOM => self::TOP,
            self::LEFT => self::RIGHT,
            self::RIGHT => self::LEFT,
        };
    }
}

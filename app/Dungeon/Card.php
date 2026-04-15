<?php

namespace App\Dungeon;


interface Card
{
    public string $id {
        get;
    }

    public string $name {
        get;
    }

    public string $description {
        get;
    }

    public int $mana {
        get;
    }

    public Rarity $rarity {
        get;
    }

    public Type $type {
        get;
    }

    public string $image {
        get;
    }

    public int $price {
        get;
    }

    public Level $level {
        get;
    }

    public function play(Dungeon $dungeon): void;

    public function toArray(): array;

    public static function fromArray(array $data): self;
}
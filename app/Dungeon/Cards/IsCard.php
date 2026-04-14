<?php

namespace App\Dungeon\Cards;

use function Tempest\Support\str;

trait IsCard
{
    private(set) string $id;

    public function __construct()
    {
        $this->id = str()->uuid()->toString();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'mana' => $this->mana,
            'rarity' => $this->rarity,
            'type' => $this->type,
            'image' => $this->image,
            'price' => $this->price,
            'level' => $this->level,
        ];
    }
}
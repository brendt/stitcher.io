<?php

namespace App\Dungeon\Cards;

use App\Dungeon\InteractsWithTile;
use function Tempest\Support\str;
use function Tempest\Mapper\map;

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
            'class' => self::class,
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'mana' => $this->mana,
            'rarity' => $this->rarity->name,
            'type' => $this->type->value,
            'image' => $this->image,
            'price' => $this->price,
            'level' => $this->level->value,
            'canInteractWithTile' => $this instanceof InteractsWithTile,
        ];
    }

    public static function fromArray(array $data): self
    {
        return map($data)->to(self::class);
    }
}
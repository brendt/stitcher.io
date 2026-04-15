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
        $data = (array) $this;

        $data['class'] = self::class;
        $data['canInteractWithTile'] = $this instanceof InteractsWithTile;
        $data['level'] = $this->level->value;
        $data['rarity'] = $this->rarity->name;
        $data['type'] = $this->type->value;

        return $data;
    }

    public static function fromArray(array $data): self
    {
        return map($data)->to(self::class);
    }
}
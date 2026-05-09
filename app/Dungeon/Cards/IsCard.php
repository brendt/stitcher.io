<?php

namespace App\Dungeon\Cards;

use App\Dungeon\ActiveCard;
use App\Dungeon\PassiveCard;
use function Tempest\Support\str;

trait IsCard
{
    private(set) string $id;

    public int $sellPrice {
        get => (int) round($this->price / 4);
    }

    public function __construct()
    {
        $this->id = str()->uuid()->toString();
    }

    public function toArray(): array
    {
        $data = (array) $this;

        $data['class'] = self::class;
        $data['canInteractWithTile'] = $this instanceof ActiveCard;
        $data['level'] = $this->level->value;
        $data['rarity'] = $this->rarity->name;
        $data['type'] = $this->type->value;
        $data['description'] = $this->description;

        if ($this instanceof ActiveCard || $this instanceof PassiveCard) {
            $data['label'] = $this->label;
        } else {
            $data['label'] = null;
        }

        return $data;
    }
}
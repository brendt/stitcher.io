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
}
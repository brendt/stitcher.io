<?php

namespace App\Dungeon\Support;


interface Random
{
    public function chance(float $percentage): bool;

    public function item(array $items): mixed;
}

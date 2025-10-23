<?php

namespace App\Support\CommandPalette;

use Tempest\Support\Arr\ImmutableArray;

interface Indexer
{
    public function index(): ImmutableArray;
}

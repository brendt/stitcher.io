<?php

namespace App\Php\Search;

use Generator;

interface Indexer
{
    public function index(): void;
}
<?php

namespace App\Support\StoredEvents;

use Tempest\Database\Query;

trait BuffersUpdates
{
    private array $queries = [];

    public function persist(): void
    {
        if ($this->queries === []) {
            return;
        }

        new Query(implode('; ', $this->queries))->execute();

        $this->queries = [];
    }
}
<?php

declare(strict_types=1);

namespace App\Analytics;

use Tempest\Support\Arr\ImmutableArray;

final readonly class Chart
{
    public function __construct(
        public ImmutableArray $labels,
        public ImmutableArray $values,
        public ?int $min = null,
    ) {}
}

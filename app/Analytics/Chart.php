<?php

declare(strict_types=1);

namespace App\Analytics;

use Tempest\Support\Arr\ImmutableArray;

final readonly class Chart
{
    public ImmutableArray $labels;

    public ImmutableArray $values;

    public int $total;

    public function __construct(
        /** @var array<string, \App\Analytics\Chartable> */
        private ImmutableArray $entries,
        public ?int $min = null,
    )
    {
        $this->labels = $entries->map(fn (Chartable $chartable) => $chartable->label);
        $this->values = $entries->map(fn (Chartable $chartable) => $chartable->value);
        $this->total = $entries->reduce(fn (int $carry, Chartable $chartable) => $carry + $chartable->value, 0);
    }
}

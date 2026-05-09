<?php

declare(strict_types=1);

namespace App\Analytics;

use Tempest\Support\Arr\ImmutableArray;
use function Tempest\Support\arr;

final class Chart
{
    public int $total {
        get => $this->datasets->first()->total;
    }

    public ImmutableArray $labels {
        get => $this->datasets->first()->labels;
    }

    public function __construct(
        public ImmutableArray $datasets,
        public bool $twoScales = false,
        public ?int $min = null,
    ) {}

    public static function forData(array $datasets): self
    {
        return new self(
            datasets: arr($datasets)
                ->map(fn (ImmutableArray $items, int|string $title) => new Dataset($title, $items))
        );
    }
}

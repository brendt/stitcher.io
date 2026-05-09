<?php

namespace App\Analytics;

use Tempest\Support\Arr\ImmutableArray;

final readonly class Dataset
{
    public ImmutableArray $labels;

    public ImmutableArray $values;

    public int $total;

    public function __construct(
        private int|string $title,
        /** @var array<string, \App\Analytics\Chartable> */
        private ImmutableArray $entries,
        public string $scale = 'y',
        public string $color = '#fe2977',
        public string|bool $pointStyle = 'circle',
    )
    {
        $this->labels = $entries->map(fn (Chartable $chartable) => $chartable->label);
        $this->values = $entries->map(fn (Chartable $chartable) => $chartable->value);
        $this->total = $entries->reduce(fn (int $carry, Chartable $chartable) => $carry + $chartable->value, 0);
    }

    public function render(): string
    {
        $pointStyle = match (true) {
            $this->pointStyle === true => "'circle'",
            $this->pointStyle === false => 'false',
            default => "'{$this->pointStyle}'",
        };

        return sprintf(
            <<<TXT
                {
                    label: "%s",
                    data: %s,
                    borderColor: "%s",
                    borderWidth: %d,
                    yAxisID: "%s",
                    pointStyle: %s
                }
            TXT,
            $this->title,
            json_encode($this->values->values()->toArray()),
            $this->color,
            2,
            $this->scale,
            $pointStyle,
        );
    }
}
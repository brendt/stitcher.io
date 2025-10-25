<?php

namespace App\PhpDocs;

use Generator;
use function Tempest\Support\path;

final class Breadcrumbs
{
    private array $breadcrumbs;
    private int $index = 0;

    public function __construct(
        private string $path,
        private string $base,
    ) {
        $this->breadcrumbs = array_filter(explode('/', $this->path));
    }

    public bool $isLast {
        get => $this->index === count($this->breadcrumbs) - 1;
    }

    public function loop(): Generator
    {
        $current = '';
        $this->index = 0;

        foreach ($this->breadcrumbs as $breadcrumb)
        {
            yield path($this->base, $current, $breadcrumb)->toString() => $breadcrumb;

            $this->index += 1;
            $current .= $breadcrumb . '/';
        }
    }
}
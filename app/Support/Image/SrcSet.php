<?php

namespace App\Support\Image;

use function Tempest\root_path;

final class SrcSet
{
    public function __construct(
        public string $src,
        public int $width,
        public int $height,
    ) {}

    public function __toString(): string
    {
        return "{$this->src} {$this->width}w";
    }

    public string $publicPath {
        get => root_path('public', $this->src);
    }
}
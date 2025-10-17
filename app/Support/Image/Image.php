<?php

namespace App\Support\Image;

use function Tempest\root_path;
use function Tempest\src_path;

final class Image
{
    public string $src;

    public ?string $alt = null;

    /** @var \App\Support\Image\SrcSet[] */
    public array $srcset = [];

    public string $srcPath {
        get => src_path('/Blog/', $this->src);
    }

    public string $publicPath {
        get => root_path('public', $this->src);
    }

    public bool $isScalable {
        get {
            $extension = pathinfo($this->src, PATHINFO_EXTENSION);

            return in_array($extension, ['jpg', 'jpeg', 'png']);
        }
    }

    public function __construct(string $src, ?string $alt = null)
    {
        $this->src = '/' . ltrim($src, '/');
        $this->alt = $alt;
    }
}

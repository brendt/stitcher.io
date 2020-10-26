<?php

namespace Brendt\Stitcher\Plugin\Twig;

use Stitcher\Renderer\Extension;

class ImageExtension implements Extension
{
    public function name(): string
    {
        return 'image';
    }

    public function path(string $path): string
    {
        return 'HELLO';
    }
}

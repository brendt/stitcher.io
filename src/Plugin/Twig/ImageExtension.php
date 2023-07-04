<?php

namespace Brendt\Stitcher\Plugin\Twig;

use Pageon\Html\Image\ImageFactory;
use Stitcher\Renderer\Extension;

class ImageExtension implements Extension
{
    private ImageFactory $imageFactory;

    public function __construct(ImageFactory $imageFactory)
    {
        $this->imageFactory = $imageFactory;
    }

    public function name(): string
    {
        return 'image';
    }

    public function render(string $path): string
    {
        $responsiveImage = $this->imageFactory->create($path);

        return <<<HTML
        <img src="{$responsiveImage->src()}" srcset="{$responsiveImage->srcset()}" sizes="{$responsiveImage->sizes()}" alt="" />
        HTML;
    }
}

<?php

namespace Brendt\Stitcher\Plugin;

use Pageon\Html\Image\ImageFactory;
use Pageon\Lib\Parsedown as LibParsedown;
use Stitcher\Renderer\Renderer;

class Parsedown extends LibParsedown
{
    /** @var \Stitcher\Renderer\Renderer */
    protected $renderer;

    public function __construct(
        ImageFactory $imageFactory,
        Renderer $renderer
    ) {
        parent::__construct($imageFactory);

        $this->renderer = $renderer;
    }

    public function parse($text): string
    {
        $ad = file_get_contents(__DIR__ . '/../../resources/view/_partials/ad.twig');

        $text = parent::parse($text);

        if (strpos($text, '{{ ad }}') !== false) {
            $text = str_replace('{{ ad }}', $ad, $text);
        } else {
            $text .= $ad;
        }

        return $text;
    }
}

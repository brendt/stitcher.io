<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use InvalidArgumentException;
use League\CommonMark\Block\Element\Paragraph;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;

class AdRenderer implements InlineRendererInterface
{
    private $google;

    private $carbon;

    public function __construct()
    {
        $this->google = file_get_contents(__DIR__ . '/../../../resources/view/_partials/ad_google.twig');
        $this->google = '';
        $this->carbon = file_get_contents(__DIR__ . '/../../../resources/view/_partials/ad_carbon.twig');
    }

    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (! $inline instanceof Text) {
            throw new InvalidArgumentException('Block must be instance of ' . Paragraph::class);
        }

        $content = $inline->getContent();

        if (strpos($content, '{{ ad:carbon }}') !== false) {
            return $this->renderCarbon($content);
        }

        if (strpos($content, '{{ ad:google }}') !== false) {
            return $this->renderGoogle($content);
        }

        return $content;
    }

    private function renderGoogle(string $content): string
    {
        $content = str_replace('{{ ad:google }}', $this->google, $content);

        return $content;
    }

    private function renderCarbon(string $content): string
    {
        $content = str_replace('{{ ad:carbon }}', $this->carbon, $content);

        $content = str_replace('{{ ad:google }}', '', $content);

        return $content;
    }
}

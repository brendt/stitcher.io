<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use InvalidArgumentException;
use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\Paragraph;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;

class AdRenderer implements InlineRendererInterface
{
    private $ad;

    public function __construct()
    {
        $this->ad = file_get_contents(__DIR__ . '/../../../resources/view/_partials/ad.twig');
    }

    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (! $inline instanceof Text) {
            throw new InvalidArgumentException('Block must be instance of ' . Paragraph::class);
        }

        $content = $inline->getContent();

        if (strpos($content, '{{ ad }}') !== false) {
            $content = str_replace('{{ ad }}', $this->ad, $content);
        }

        return $content;
    }
}

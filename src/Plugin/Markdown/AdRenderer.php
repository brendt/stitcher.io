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

    private array $ctas = [
        '{{ cta:mail }}' => __DIR__ . '/../../../resources/view/_partials/cta_mail.twig',
        '{{ cta:diary }}' => __DIR__ . '/../../../resources/view/_partials/cta_diary.twig',
        '{{ cta:dynamic }}' => __DIR__ . '/../../../resources/view/_partials/cta_dynamic.twig',
        '{{ cta:blogs_mail }}' => __DIR__ . '/../../../resources/view/_partials/cta_blogs_mail.twig',
        '{{ cta:blogs_mail_short }}' => __DIR__ . '/../../../resources/view/_partials/cta_blogs_mail_short.twig',
        '{{ cta:blogs_more }}' => __DIR__ . '/../../../resources/view/_partials/cta_blogs_more.twig',
        '{{ cta:blogs_index }}' => __DIR__ . '/../../../resources/view/_partials/cta_blogs_index.twig',
    ];

    public function __construct()
    {
        $this->google = file_get_contents(__DIR__ . '/../../../resources/view/_partials/ad_google.twig');
        $this->carbon = file_get_contents(__DIR__ . '/../../../resources/view/_partials/ad_carbon.twig');

        foreach ($this->ctas as $key => $path) {
            $this->ctas[$key] = file_get_contents($path);
        }
    }

    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (! $inline instanceof Text) {
            throw new InvalidArgumentException('Block must be instance of ' . Paragraph::class);
        }

        $content = $inline->getContent();

        foreach ($this->ctas as $key => $cta) {
            $content = str_replace($key, $cta, $content);
        }

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

<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use Illuminate\Support\Str;
use InvalidArgumentException;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

class AdRenderer implements NodeRendererInterface
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
        '{{ cta:81 }}' => __DIR__ . '/../../../resources/view/_partials/cta_81.twig',
        '{{ cta:like }}' => __DIR__ . '/../../../resources/view/_partials/cta_like.twig',
        '{{ cta:packagist }}' => __DIR__ . '/../../../resources/view/_partials/ad_packagist.twig',
    ];

    private int $stamp = 0;

    public function __construct()
    {
        $this->google = file_get_contents(__DIR__ . '/../../../resources/view/_partials/ad_google.twig');
        $this->carbon = file_get_contents(__DIR__ . '/../../../resources/view/_partials/ad_carbon.twig');

        foreach ($this->ctas as $key => $path) {
            $this->ctas[$key] = file_get_contents($path);
        }
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (! $node instanceof Text) {
            throw new InvalidArgumentException('Block must be instance of ' . Text::class);
        }

        $content = $node->getLiteral();

        foreach ($this->ctas as $key => $cta) {
            while (Str::contains($content, $key)) {
                $stampedCta = str_replace('{{ stamp }}', $this->stamp, $cta);

                $content = Str::replaceFirst($key, $stampedCta, $content);

                $this->stamp += 1;
            }
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

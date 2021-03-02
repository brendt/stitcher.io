<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use Illuminate\Support\Str;
use InvalidArgumentException;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use Pageon\Config;

class LinkRenderer implements InlineRendererInterface
{
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer): HtmlElement
    {
        if (! $inline instanceof Link) {
            throw new InvalidArgumentException('Inline must be instance of ' . Link::class);
        }

        $attributes = [];

        $url = $inline->getUrl();

        if (strpos($url, '*') === 0) {
            $url = substr($url, 1);

            $attributes['target'] = '_blank';
        }

        $attributes['href'] = $url;

        $pingUrl = Config::get('environment') === 'local'
            ? 'http://analytics.stitcher.io.test/r'
            : 'https://analytics.stitcher.io/r';

        if (! Str::startsWith($url, '#')) {
            $attributes['ping'] = $pingUrl;
        }

        return new HtmlElement(
            'a',
            $attributes,
            $htmlRenderer->renderInlines($inline->children())
        );
    }
}

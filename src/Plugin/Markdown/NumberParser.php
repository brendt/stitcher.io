<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\InlineParserContext;

class NumberParser implements InlineParserInterface
{
    public function getCharacters(): array
    {
        return ['('];
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();

        $match = $cursor->match('/\(\(\#[0-9\,\.]+\#\)\)/');

        if (! $match) {
            return false;
        }

        $abbreviation = str_replace(['((#', '#))'], ['<span class="number">', '</span>'], $match);

        $inlineContext->getContainer()->appendChild(new Text($abbreviation));

        return true;
    }
}

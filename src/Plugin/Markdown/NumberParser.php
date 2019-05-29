<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\AbstractInlineParser;
use League\CommonMark\InlineParserContext;

class NumberParser extends AbstractInlineParser
{
    public function getCharacters()
    {
        return ['('];
    }

    public function parse(InlineParserContext $inlineContext)
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

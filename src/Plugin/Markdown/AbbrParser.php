<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\AbstractInlineParser;
use League\CommonMark\InlineParserContext;

class AbbrParser extends AbstractInlineParser
{
    public function getCharacters()
    {
        return ['('];
    }

    public function parse(InlineParserContext $inlineContext)
    {
        $cursor = $inlineContext->getCursor();

        $nextChar = $cursor->peek(1);

        if ($nextChar !== '(') {
            return false;
        }

        $match = $cursor->match('/\(\([A-Za-z0-9_\s]+\)\)/');

        if (! $match) {
            return false;
        }

        $abbreviation = str_replace(['((', '))'], ['<abbr>', '</abbr>'], $match);

        $inlineContext->getContainer()->appendChild(new Text($abbreviation));

        return true;
    }
}

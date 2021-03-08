<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\InlineParserContext;

class AbbrParser implements InlineParserInterface
{
    public function getCharacters(): array
    {
        return ['('];
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();

        $nextChar = $cursor->peek(1);

        if ($nextChar !== '(') {
            return false;
        }

        $match = $cursor->match('/\(\([A-Za-z0-9_\s\.]+\)\)/');

        if (! $match) {
            return false;
        }

        $abbreviation = str_replace(['((', '))'], ['<abbr>', '</abbr>'], $match);

        $inlineContext->getContainer()->appendChild(new Text($abbreviation));

        return true;
    }
}

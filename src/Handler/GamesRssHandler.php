<?php

namespace Brendt\Stitcher\Handler;

class GamesRssHandler extends RssHandler
{
    protected function getSourcePath(): string
    {
        return 'src/content/games.yaml';
    }
}

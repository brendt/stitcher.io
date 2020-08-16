<?php

namespace Brendt\Stitcher\Handler;

class PodcastsRssHandler extends RssHandler
{
    protected function getSourcePath(): string
    {
        return 'src/content/podcasts.yaml';
    }
}

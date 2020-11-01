<?php

namespace Brendt\Stitcher\Handler;

class BlogsForDevsRssHandler extends RssHandler
{
    protected function getSourcePath(): string
    {
        return 'src/content/blogs-for-devs.yaml';
    }
}

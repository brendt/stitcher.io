<?php

namespace Brendt\Stitcher\Handler;

class BlogRssHandler extends RssHandler
{
    protected function getSourcePath(): string
    {
        return 'src/content/blog.yaml';
    }
}

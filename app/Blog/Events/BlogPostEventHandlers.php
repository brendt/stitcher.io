<?php

namespace App\Blog\Events;

use Tempest\Console\HasConsole;
use Tempest\Discovery\SkipDiscovery;
use Tempest\EventBus\EventBus;

#[SkipDiscovery]
final class BlogPostEventHandlers
{
    use HasConsole;

    public function __construct(
        private readonly EventBus $eventBus,
    ) {}

    public function onAllBlogPostsRetrieved(AllBlogPostsRetrieved $event): void
    {
        $this->info("Parsing blog posts…");
    }
}
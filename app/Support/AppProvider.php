<?php

namespace App\Support;

use App\Blog\Events\BlogPostEventHandlers;
use Tempest\Console\ConsoleApplication;
use Tempest\Container\Container;
use Tempest\Core\Application;
use Tempest\Core\KernelEvent;
use Tempest\EventBus\EventBus;
use Tempest\EventBus\EventHandler;

final readonly class AppProvider
{
    public function __construct(
        private Application $application,
        private EventBus $eventBus,
        private Container $container,
    ) {}

    #[EventHandler(KernelEvent::BOOTED)]
    public function onKernelBoot(): void
    {
        if ($this->application instanceof ConsoleApplication) {
            $handlers = $this->container->get(BlogPostEventHandlers::class);

            $this->eventBus->listen($handlers->onAllBlogPostsRetrieved(...));
        }
    }
}
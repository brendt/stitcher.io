<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerMinute;

use App\Analytics\PageVisited;
use App\Support\StoredEvents\BufferedProjector;
use App\Support\StoredEvents\BuffersUpdates;
use App\Support\StoredEvents\Projector;
use Tempest\Container\Singleton;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\EventBus\EventHandler;

#[Singleton]
final class VisitsPerMinuteProjector implements Projector, BufferedProjector
{
    use BuffersUpdates;

    public function replay(object $event): void
    {
        if ($event instanceof PageVisited) {
            $this->onPageVisited($event);
        }
    }

    public function clear(): void
    {
        new QueryBuilder('visits_per_minute')
            ->delete()
            ->allowAll()
            ->execute();
    }

    #[EventHandler]
    public function onPageVisited(PageVisited $pageVisited): void
    {
        $time = $pageVisited->visitedAt->format('Y-m-d H:i') . ':00';

        $this->queries[] = sprintf(
            "INSERT INTO `visits_per_minute` (`time`, `count`) VALUES (\"%s\", %s) ON DUPLICATE KEY UPDATE `count` = `count` + 1",
            $time,
            1
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerPostPerDay;

use App\Analytics\PageVisited;
use App\Support\StoredEvents\BufferedProjector;
use App\Support\StoredEvents\BuffersUpdates;
use App\Support\StoredEvents\Projector;
use Tempest\Container\Singleton;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\EventBus\EventHandler;

#[Singleton]
final class VisitsPerPostPerDayProjector implements Projector, BufferedProjector
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
        new QueryBuilder(VisitsPerPostPerDay::class)
            ->delete()
            ->allowAll()
            ->execute();
    }

    #[EventHandler]
    public function onPageVisited(PageVisited $pageVisited): void
    {
        $date = $pageVisited->visitedAt->format('Y-m-d') . ' 00:00:00';

        $this->queries[] = sprintf(
            "INSERT INTO `visits_per_post_per_day` (`uri`, `date`, `count`) VALUES (\"%s\", \"%s\", %s) ON DUPLICATE KEY UPDATE `count` = `count` + 1",
            $pageVisited->url,
            $date,
            1,
        );
    }
}

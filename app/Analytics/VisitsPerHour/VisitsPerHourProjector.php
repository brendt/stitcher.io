<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerHour;

use App\Analytics\PageVisited;
use App\Support\StoredEvents\BufferedProjector;
use App\Support\StoredEvents\BuffersUpdates;
use App\Support\StoredEvents\Projector;
use Tempest\Container\Singleton;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Query;
use Tempest\EventBus\EventHandler;

#[Singleton]
final class VisitsPerHourProjector implements Projector, BufferedProjector
{
    private array $inserts = [];

    #[EventHandler]
    public function onPageVisited(PageVisited $pageVisited): void
    {
        $hour = $pageVisited->visitedAt->format('Y-m-d H') . ':00:00';

        $this->inserts[] = $hour;
    }

    public function persist(): void
    {
        if ($this->inserts === []) {
            return;
        }

        $query = new Query(sprintf(
            'INSERT INTO `visits_per_hour` (`hour`, `count`) VALUES %s ON DUPLICATE KEY UPDATE `count` = `count` + 1',
            implode(',', array_map(
                fn (string $hour) => "(\"$hour\",1)",
                $this->inserts,
            )),
        ));

        $query->execute();

        $this->inserts = [];
    }

    public function replay(object $event): void
    {
        if ($event instanceof PageVisited) {
            $this->onPageVisited($event);
        }
    }

    public function clear(): void
    {
        new QueryBuilder('visits_per_hour')
            ->delete()
            ->allowAll()
            ->execute();
    }
}

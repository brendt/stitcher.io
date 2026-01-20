<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerDay;

use App\Analytics\PageVisited;
use App\Support\StoredEvents\BuffersUpdates;
use App\Support\StoredEvents\Projector;
use Tempest\Container\Singleton;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Query;
use Tempest\EventBus\EventHandler;
use function Tempest\Database\query;

#[Singleton]
final class VisitsPerDayProjector implements Projector, BuffersUpdates
{
    private array $queries = [];

    public function replay(object $event): void
    {
        if ($event instanceof PageVisited) {
            $this->onPageVisited($event);
        }
    }

    public function clear(): void
    {
        new QueryBuilder('visits_per_day')
            ->delete()
            ->allowAll()
            ->execute();
    }

    public function persist(): void
    {
        if ($this->queries === []) {
            return;
        }

        new Query(implode(' ', $this->queries))->execute();

        $this->queries = [];
    }

    #[EventHandler]
    public function onPageVisited(PageVisited $pageVisited): void
    {
        $date = $pageVisited->visitedAt->setTime(0, 0);

        $this->queries[] = sprintf(
            "INSERT INTO `visits_per_day` (`date`, `count`) VALUES (\"%s\", %s) ON DUPLICATE KEY UPDATE `count` = `count` + 1;",
            $date->format('Y-m-d H:i:s'),
            1
        );
    }
}

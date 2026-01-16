<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerDay;

use App\Analytics\PageVisited;
use App\Support\StoredEvents\Projector;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Query;
use Tempest\EventBus\EventHandler;

final readonly class VisitsPerDayProjector implements Projector
{
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

    #[EventHandler]
    public function onPageVisited(PageVisited $pageVisited): void
    {
        $date = $pageVisited->visitedAt->setTime(0, 0);

        new Query(<<<SQL
        INSERT INTO visits_per_day (`date`, `count`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `count` = `count` + 1
        SQL, [
            $date,
            1
        ])->execute();
    }
}

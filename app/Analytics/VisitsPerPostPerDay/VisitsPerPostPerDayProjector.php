<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerPostPerDay;

use App\Analytics\PageVisited;
use App\Support\StoredEvents\Projector;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Query;
use Tempest\EventBus\EventHandler;

final readonly class VisitsPerPostPerDayProjector implements Projector
{
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
        $date = $pageVisited->visitedAt->setTime(0, 0);

        new Query(<<<SQL
        INSERT INTO `visits_per_post_per_day` (`uri`, `date`, `count`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `count` = `count` + 1
        SQL, [
            $pageVisited->url,
            $date,
            1
        ])->execute();
    }
}

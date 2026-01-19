<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerPostPerWeek;

use App\Analytics\PageVisited;
use App\Support\StoredEvents\Projector;
use DateInterval;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Exceptions\QueryWasInvalid;
use Tempest\Database\Query;
use Tempest\DateTime\DateTime;
use Tempest\EventBus\EventHandler;

final readonly class VisitsPerPostPerWeekProjector implements Projector
{
    public function replay(object $event): void
    {
        if ($event instanceof PageVisited) {
            $this->onPageVisited($event);
        }
    }

    public function clear(): void
    {
        new QueryBuilder(VisitsPerPostPerWeek::class)
            ->delete()
            ->allowAll()
            ->execute();
    }

    #[EventHandler]
    public function onPageVisited(PageVisited $pageVisited): void
    {
        $date = DateTime::parse($pageVisited->visitedAt)->startOfDay()->startOfWeek();

        try {
            new Query(<<<SQL
            INSERT INTO `visits_per_post_per_week` (`uri`, `date`, `count`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `count` = `count` + 1
            SQL, [
                $pageVisited->url,
                $date,
                1
            ])->execute();
        } catch (QueryWasInvalid) {
            // Skip this one
        }
    }
}

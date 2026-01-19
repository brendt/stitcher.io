<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerYear;

use App\Analytics\PageVisited;
use App\Support\StoredEvents\Projector;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Query;
use Tempest\EventBus\EventHandler;
use function Tempest\Database\inspect;

final readonly class VisitsPerYearProjector implements Projector
{
    public function replay(object $event): void
    {
        if ($event instanceof PageVisited) {
            $this->onPageVisited($event);
        }
    }

    public function clear(): void
    {
        new QueryBuilder(VisitsPerYear::class)
            ->delete()
            ->allowAll()
            ->execute();
    }

    #[EventHandler]
    public function onPageVisited(PageVisited $pageVisited): void
    {
        $date = $pageVisited->visitedAt->format('Y') . '-01-01';

        new Query(<<<SQL
        INSERT INTO `visits_per_year` (`date`, `count`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `count` = `count` + 1
        SQL, [
            $date,
            1
        ])->execute();
    }
}

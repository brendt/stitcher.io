<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerHour;

use App\Analytics\PageVisited;
use App\Support\StoredEvents\Projector;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Query;
use Tempest\EventBus\EventHandler;
use function Tempest\Database\inspect;

final readonly class VisitsPerHourProjector implements Projector
{
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

    #[EventHandler]
    public function onPageVisited(PageVisited $pageVisited): void
    {
        $hour = $pageVisited->visitedAt->format('Y-m-d H') . ':00:00';
        $table = inspect(VisitsPerHour::class)->getTableName();

        new Query(<<<SQL
        INSERT INTO $table (`hour`, `count`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `count` = `count` + 1
        SQL, [
            $hour,
            1
        ])->execute();
    }
}

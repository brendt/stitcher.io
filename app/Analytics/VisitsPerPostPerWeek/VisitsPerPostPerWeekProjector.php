<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerPostPerWeek;

use App\Analytics\PageVisited;
use App\Support\StoredEvents\Projector;
use DateInterval;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Query;
use Tempest\EventBus\EventHandler;
use function Tempest\Database\inspect;

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
        $date = $pageVisited->visitedAt->setTime(0, 0);

        while($date->format('l') !== 'Monday') {
            $date = $date->sub(new DateInterval('P1D'));
        }

        $table = inspect(VisitsPerPostPerWeek::class)->getTableName();

        new Query(<<<SQL
        INSERT INTO $table (`uri`, `date`, `count`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `count` = `count` + 1
        SQL, [
            $pageVisited->url,
            $date,
            1
        ])->execute();
    }
}

<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerYear;

use App\Analytics\PageVisited;
use App\Support\StoredEvents\BufferedProjector;
use App\Support\StoredEvents\Projector;
use Tempest\Container\Singleton;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Query;
use Tempest\EventBus\EventHandler;

#[Singleton]
final class VisitsPerYearProjector implements Projector, BufferedProjector
{
    private array $inserts = [];

    #[EventHandler]
    public function onPageVisited(PageVisited $pageVisited): void
    {
        $date = $pageVisited->visitedAt->format('Y') . '-01-01';

        $this->inserts[$date] = ($this->inserts[$date] ?? 0) + 1;
    }


    public function onPageVisitedArray(array $event): void
    {
        $date = substr($event['createdAt'], 0, 4);

        $this->inserts[$date] = ($this->inserts[$date] ?? 0) + 1;
    }

    public function persist(): void
    {
        if ($this->inserts === []) {
            return;
        }

        $values = [];

        foreach ($this->inserts as $date => $count) {
            $values[] = "(\"{$date}-01-01\",{$count})";
        }

        $query = new Query(sprintf(
            'INSERT INTO `visits_per_year` (`date`, `count`) VALUES %s ON DUPLICATE KEY UPDATE `count` = `count` + VALUES(`count`)',
            implode(',', $values),
        ));

        $query->execute();

        $this->inserts = [];
    }

    public function replay(array|object $event): void
    {
        if (is_array($event)) {
            $this->onPageVisitedArray($event);
        } elseif ($event instanceof PageVisited) {
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
}

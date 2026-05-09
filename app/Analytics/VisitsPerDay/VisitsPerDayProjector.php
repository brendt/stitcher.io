<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerDay;

use App\Analytics\PageVisited;
use App\Support\StoredEvents\BufferedProjector;
use App\Support\StoredEvents\Projector;
use Tempest\Container\Singleton;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Query;
use Tempest\EventBus\EventHandler;
use function Tempest\Support\arr;

#[Singleton]
final class VisitsPerDayProjector implements Projector, BufferedProjector
{
    private array $inserts = [];

    #[EventHandler]
    public function onPageVisited(PageVisited $pageVisited): void
    {
        $this->inserts[$pageVisited->visitedAt->format('Y-m-d')] ??= 0;
        $this->inserts[$pageVisited->visitedAt->format('Y-m-d')]++;
    }

    public function persist(): void
    {
        if ($this->inserts === []) {
            return;
        }

        $query = new Query(sprintf(
            'INSERT INTO `visits_per_day` (`date`, `count`) VALUES %s ON DUPLICATE KEY UPDATE `count` = `count` + VALUES(`count`)',
            arr($this->inserts)
                ->map(fn (int $count, string $date) => "(\"{$date} 00:00:00\", $count)")
                ->implode(','),
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
        new QueryBuilder('visits_per_day')
            ->delete()
            ->allowAll()
            ->execute();
    }
}

<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerMonth;

use App\Analytics\PageVisited;
use App\Support\StoredEvents\BufferedProjector;
use App\Support\StoredEvents\Projector;
use Tempest\Container\Singleton;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Query;
use Tempest\EventBus\EventHandler;
use function Tempest\Support\arr;

#[Singleton]
final class VisitsPerMonthProjector implements Projector, BufferedProjector
{
    private array $inserts = [];

    #[EventHandler]
    public function onPageVisited(PageVisited $pageVisited): void
    {
        $this->inserts[$pageVisited->visitedAt->format('Y-m')] ??= 0;
        $this->inserts[$pageVisited->visitedAt->format('Y-m')]++;
    }

    public function persist(): void
    {
        if ($this->inserts === []) {
            return;
        }

        $query = new Query(sprintf(
            'INSERT INTO `visits_per_month` (`date`, `count`) VALUES %s ON DUPLICATE KEY UPDATE `count` = `count` + VALUES(`count`)',
            arr($this->inserts)
                ->map(fn (int $count, string $date) => "(\"{$date}-01\", $count)")
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
        new QueryBuilder(VisitsPerMonth::class)
            ->delete()
            ->allowAll()
            ->execute();
    }
}

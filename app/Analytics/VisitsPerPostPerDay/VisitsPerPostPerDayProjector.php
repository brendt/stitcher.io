<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerPostPerDay;

use App\Analytics\PageVisited;
use App\Support\StoredEvents\BufferedProjector;
use App\Support\StoredEvents\Projector;
use Tempest\Container\Singleton;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Query;
use Tempest\EventBus\EventHandler;
use function Tempest\Support\arr;

#[Singleton]
final class VisitsPerPostPerDayProjector implements Projector, BufferedProjector
{
    private array $inserts = [];

    #[EventHandler]
    public function onPageVisited(PageVisited $pageVisited): void
    {
        $this->inserts[$pageVisited->url][$pageVisited->visitedAt->format('Y-m-d')] ??= 0;
        $this->inserts[$pageVisited->url][$pageVisited->visitedAt->format('Y-m-d')]++;
    }

    public function persist(): void
    {
        if ($this->inserts === []) {
            return;
        }

        $query = new Query(sprintf(
            'INSERT INTO `visits_per_post_per_day` (`uri`, `date`, `count`) VALUES %s ON DUPLICATE KEY UPDATE `count` = `count` + VALUES(`count`)',
            arr($this->inserts)
                ->map(fn (array $days, string $uri) => arr($days)
                    ->map(fn (int $count, string $date) => "(\"{$uri}\", \"{$date}\", {$count})")
                    ->implode(','),
                )
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
        new QueryBuilder(VisitsPerPostPerDay::class)
            ->delete()
            ->allowAll()
            ->execute();
    }
}

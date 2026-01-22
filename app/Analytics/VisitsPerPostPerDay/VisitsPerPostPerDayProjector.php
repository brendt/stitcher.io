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

#[Singleton]
final class VisitsPerPostPerDayProjector implements Projector, BufferedProjector
{
    private array $inserts = [];

    #[EventHandler]
    public function onPageVisited(PageVisited $pageVisited): void
    {
        $this->inserts[] = [
            $pageVisited->url,
            $pageVisited->visitedAt->format('Y-m-d') . ' 00:00:00'
        ];
    }

    public function persist(): void
    {
        if ($this->inserts === []) {
            return;
        }

        $query = new Query(sprintf(
            'INSERT INTO `visits_per_post_per_day` (`uri`, `date`, `count`) VALUES %s ON DUPLICATE KEY UPDATE `count` = `count` + 1',
            implode(',', array_map(
                fn (array $data) => sprintf('("%s", "%s", 1)', ...$data),
                $this->inserts,
            )),
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

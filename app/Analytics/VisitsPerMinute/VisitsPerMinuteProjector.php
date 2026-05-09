<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerMinute;

use App\Analytics\PageVisited;
use App\Support\StoredEvents\BufferedProjector;
use App\Support\StoredEvents\Projector;
use Tempest\Container\Singleton;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Query;
use Tempest\EventBus\EventHandler;

#[Singleton]
final class VisitsPerMinuteProjector implements Projector, BufferedProjector
{
    private array $inserts = [];

    #[EventHandler]
    public function onPageVisited(PageVisited $pageVisited): void
    {
        $time = $pageVisited->visitedAt->format('Y-m-d H:i') . ':00';

        $this->inserts[] = $time;
    }

    public function persist(): void
    {
        if ($this->inserts === []) {
            return;
        }

        $query = new Query(sprintf(
            'INSERT INTO `visits_per_minute` (`time`, `count`) VALUES %s ON DUPLICATE KEY UPDATE `count` = `count` + 1',
            implode(',', array_map(
                fn (string $time) => "(\"$time\",1)",
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
        new QueryBuilder('visits_per_minute')
            ->delete()
            ->allowAll()
            ->execute();
    }
}

<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerYear;

use App\Analytics\PageVisited;
use App\Support\StoredEvents\BufferedProjector;
use App\Support\StoredEvents\BuffersUpdates;
use App\Support\StoredEvents\Projector;
use Tempest\Container\Singleton;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Query;
use Tempest\EventBus\EventHandler;

#[Singleton]
final class VisitsPerYearProjector implements Projector, BufferedProjector
{
    use BuffersUpdates;

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

        $this->queries[] = sprintf(
            "INSERT INTO `visits_per_year` (`date`, `count`) VALUES (\"%s\", %s) ON DUPLICATE KEY UPDATE `count` = `count` + 1",
            $date,
            1
        );
    }
}

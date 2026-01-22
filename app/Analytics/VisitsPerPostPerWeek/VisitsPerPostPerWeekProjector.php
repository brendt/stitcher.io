<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerPostPerWeek;

use App\Analytics\PageVisited;
use App\Support\StoredEvents\BufferedProjector;
use App\Support\StoredEvents\BuffersUpdates;
use App\Support\StoredEvents\Projector;
use DateInterval;
use Tempest\Container\Singleton;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Query;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\FormatPattern;
use Tempest\EventBus\EventHandler;

#[Singleton]
final class VisitsPerPostPerWeekProjector implements Projector, BufferedProjector
{
    private array $inserts = [];

    #[EventHandler]
    public function onPageVisited(PageVisited $pageVisited): void
    {
        $date = DateTime::parse($pageVisited->visitedAt)->startOfDay()->startOfWeek()->format(FormatPattern::SQL_DATE_TIME);

        $this->inserts[] = [$pageVisited->url, $date];
    }

    public function persist(): void
    {
        if ($this->inserts === []) {
            return;
        }

        $query = new Query(sprintf(
            'INSERT INTO `visits_per_post_per_week` (`uri`, `date`, `count`) VALUES %s ON DUPLICATE KEY UPDATE `count` = `count` + 1',
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
        new QueryBuilder(VisitsPerPostPerWeek::class)
            ->delete()
            ->allowAll()
            ->execute();
    }
}

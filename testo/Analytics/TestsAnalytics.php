<?php

declare(strict_types=1);

namespace Tests\Testo\Analytics;

use App\Analytics\PageVisited;
use DateTimeImmutable;
use Testo\Lifecycle\BeforeTest;

use function Tempest\EventBus\event;

trait TestsAnalytics
{
    /**
     * Projections accumulate, so every analytics test needs an empty set of tables.
     *
     * Runs after `IntegrationTestCase::setUpTempest()`, which holds a higher priority.
     */
    #[BeforeTest]
    public function resetAnalyticsTables(): void
    {
        $this->database->reset();
    }

    private function triggerVisit(
        string $date = '2026-01-01 10:00:10',
        string $uri = 'https://example.com',
    ): void {
        event(new PageVisited(
            url: $uri,
            visitedAt: new DateTimeImmutable($date),
            ip: '127.0.0.1',
            userAgent: 'Test',
            raw: 'Test',
        ));
    }
}

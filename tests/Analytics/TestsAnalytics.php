<?php

namespace Tests\Analytics;

use App\Analytics\PageVisited;
use DateTimeImmutable;
use function Tempest\EventBus\event;

trait TestsAnalytics
{

    public function setUp(): void
    {
        parent::setUp();

        $this->database->reset();
    }

    private function triggerVisit(
        string $date = '2026-01-01 10:00:10',
        string $uri = 'https://example.com',
    ): void
    {
        event(new PageVisited(
            url: $uri,
            visitedAt: new DateTimeImmutable($date),
            ip: '127.0.0.1',
            userAgent: 'Test',
            raw: 'Test',
        ));
    }
}
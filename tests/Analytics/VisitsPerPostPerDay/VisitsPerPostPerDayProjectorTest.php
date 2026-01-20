<?php

namespace Tests\Analytics\VisitsPerPostPerDay;

use App\Analytics\VisitsPerPostPerDay\VisitsPerPostPerDayProjector;
use PHPUnit\Framework\Attributes\Test;
use Tests\Analytics\TestsAnalytics;
use Tests\IntegrationTestCase;

class VisitsPerPostPerDayProjectorTest extends IntegrationTestCase
{
    use TestsAnalytics;

    #[Test]
    public function events_are_persisted(): void
    {
        $this->triggerVisit('2026-01-05 10:00:00', '/a');

        $this->database->assertTableHasRow(
            table: 'visits_per_post_per_day',
            date: '2026-01-05 00:00:00',
            uri: '/a',
            count: 1,
        );

        $this->triggerVisit('2026-01-05 10:00:00', '/a');
        $this->triggerVisit('2026-01-05 10:00:00', '/b');
        $this->triggerVisit('2026-01-06 10:00:00', '/a');
        $this->triggerVisit('2026-01-06 10:00:00', '/b');

        $this->database->assertTableHasRow(
            table: 'visits_per_post_per_day',
            date: '2026-01-05 00:00:00',
            uri: '/a',
            count: 2,
        );

        $this->database->assertTableHasRow(
            table: 'visits_per_post_per_day',
            date: '2026-01-05 00:00:00',
            uri: '/b',
            count: 1,
        );

        $this->database->assertTableHasRow(
            table: 'visits_per_post_per_day',
            date: '2026-01-06 00:00:00',
            uri: '/a',
            count: 1,
        );

        $this->database->assertTableHasRow(
            table: 'visits_per_post_per_day',
            date: '2026-01-06 00:00:00',
            uri: '/b',
            count: 1,
        );
    }

    #[Test]
    public function replay_test(): void
    {
        $this->triggerVisit('2026-01-10 10:00:00', '/a');
        $this->triggerVisit('2026-01-10 10:00:00', '/a');
        $this->triggerVisit('2026-01-10 10:00:00', '/b');

        $this->console->call(sprintf('replay "%s" --force', VisitsPerPostPerDayProjector::class))->assertSuccess();

        $this->database->assertTableHasRow(
            table: 'visits_per_post_per_day',
            date: '2026-01-10 00:00:00',
            uri: '/a',
            count: 2,
        );

        $this->database->assertTableHasRow(
            table: 'visits_per_post_per_day',
            date: '2026-01-10 00:00:00',
            uri: '/b',
            count: 1,
        );
    }
}

<?php

declare(strict_types=1);

namespace Tests\Testo\Analytics\VisitsPerPostPerDay;

use App\Analytics\VisitsPerPostPerDay\VisitsPerPostPerDayProjector;
use Testo\Test;
use Tests\Testo\Analytics\TestsAnalytics;
use Tests\Testo\Support\IntegrationTestCase;

#[Test]
class VisitsPerPostPerDayProjectorTest extends IntegrationTestCase
{
    use TestsAnalytics;

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

    public function replay_test(): void
    {
        $this->triggerVisit('2026-01-10 10:00:00', '/a');
        $this->triggerVisit('2026-01-10 10:00:00', '/a');
        $this->triggerVisit('2026-01-10 10:00:00', '/b');

        $this->console->call(sprintf('replay "%s" --force', VisitsPerPostPerDayProjector::class))->succeeds();

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

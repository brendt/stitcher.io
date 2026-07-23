<?php

namespace Tests\Analytics\VisitsPerPostPerWeek;

use App\Analytics\VisitsPerPostPerWeek\VisitsPerPostPerWeekProjector;
use Tempest\Testing\Test;
use Tempest\Testing\Testers\Console\TestsConsole;
use Tempest\Testing\Testers\Database\TestsDatabase;
use Tempest\Testing\Testers\HasContainer;
use Tests\Analytics\TestsAnalytics;

class VisitsPerPostPerWeekProjectorTest
{
    use TestsAnalytics;
    use TestsDatabase;
    use TestsConsole;
    use HasContainer;

    #[Test]
    public function events_are_persisted(): void
    {
        $this->database->reset();

        $this->triggerVisit('2026-01-06 ', '/a');

        $this->database->assertTableHasRow(
            table: 'visits_per_post_per_week',
            date: '2026-01-05',
            uri: '/a',
            count: 1,
        );

        $this->triggerVisit('2026-01-06 ', '/a');
        $this->triggerVisit('2026-01-06 ', '/b');
        $this->triggerVisit('2026-01-13 ', '/a');
        $this->triggerVisit('2026-01-13 ', '/b');

        $this->database->assertTableHasRow(
            table: 'visits_per_post_per_week',
            date: '2026-01-05',
            uri: '/a',
            count: 2,
        );

        $this->database->assertTableHasRow(
            table: 'visits_per_post_per_week',
            date: '2026-01-05',
            uri: '/b',
            count: 1,
        );

        $this->database->assertTableHasRow(
            table: 'visits_per_post_per_week',
            date: '2026-01-12',
            uri: '/a',
            count: 1,
        );

        $this->database->assertTableHasRow(
            table: 'visits_per_post_per_week',
            date: '2026-01-12',
            uri: '/b',
            count: 1,
        );
    }

    #[Test]
    public function replay_test(): void
    {
        $this->database->reset();

        $this->triggerVisit('2026-01-06 ', '/a');
        $this->triggerVisit('2026-01-06 ', '/a');
        $this->triggerVisit('2026-01-06 ', '/b');

        $this->console->call(sprintf('replay "%s" --force', VisitsPerPostPerWeekProjector::class))->succeeds();

        $this->database->assertTableHasRow(
            table: 'visits_per_post_per_week',
            date: '2026-01-05',
            uri: '/a',
            count: 2,
        );

        $this->database->assertTableHasRow(
            table: 'visits_per_post_per_week',
            date: '2026-01-05',
            uri: '/b',
            count: 1,
        );
    }
}

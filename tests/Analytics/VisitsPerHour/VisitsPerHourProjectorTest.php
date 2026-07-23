<?php

namespace Tests\Analytics\VisitsPerHour;

use App\Analytics\VisitsPerHour\VisitsPerHourProjector;
use Tempest\Testing\Test;
use Tempest\Testing\Testers\Console\TestsConsole;
use Tempest\Testing\Testers\Database\TestsDatabase;
use Tempest\Testing\Testers\HasContainer;
use Tests\Analytics\TestsAnalytics;

class VisitsPerHourProjectorTest
{
    use TestsAnalytics;
    use TestsDatabase;
    use TestsConsole;
    use HasContainer;

    #[Test]
    public function events_are_persisted(): void
    {
        $this->database->reset();

        $this->triggerVisit('2026-01-01 01:10:00');

        $this->database->assertTableHasRow(
            table: 'visits_per_hour',
            hour: '2026-01-01 01:00:00',
            count: 1,
        );

        $this->triggerVisit('2026-01-01 01:10:00');
        $this->triggerVisit('2026-01-01 02:10:00');

        $this->database->assertTableHasRow(
            table: 'visits_per_hour',
            hour: '2026-01-01 01:00:00',
            count: 2,
        );

        $this->database->assertTableHasRow(
            table: 'visits_per_hour',
            hour: '2026-01-01 02:00:00',
            count: 1,
        );
    }

    #[Test]
    public function replay_test(): void
    {
        $this->database->reset();

        $this->triggerVisit('2026-01-01 01:00:00');
        $this->triggerVisit('2026-01-01 01:00:00');
        $this->triggerVisit('2026-01-01 01:00:00');

        $this->console->call(sprintf('replay "%s" --force', VisitsPerHourProjector::class))->succeeds();

        $this->database->assertTableHasRow(
            table: 'visits_per_hour',
            hour: '2026-01-01 01:00:00',
            count: 3,
        );
    }
}

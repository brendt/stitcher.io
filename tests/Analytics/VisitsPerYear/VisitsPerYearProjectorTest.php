<?php

namespace Tests\Analytics\VisitsPerYear;

use App\Analytics\VisitsPerYear\VisitsPerYearProjector;
use Tempest\Testing\Test;
use Tempest\Testing\Testers\Console\TestsConsole;
use Tempest\Testing\Testers\Database\TestsDatabase;
use Tempest\Testing\Testers\HasContainer;
use Tests\Analytics\TestsAnalytics;

class VisitsPerYearProjectorTest
{
    use TestsAnalytics;
    use TestsDatabase;
    use TestsConsole;
    use HasContainer;

    #[Test]
    public function events_are_persisted(): void
    {
        $this->database->reset();

        $this->triggerVisit('2026-01-05');

        $this->database->assertTableHasRow(
            table: 'visits_per_year',
            date: '2026-01-01',
            count: 1,
        );

        $this->triggerVisit('2026-01-10');
        $this->triggerVisit('2025-02-10');

        $this->database->assertTableHasRow(
            table: 'visits_per_year',
            date: '2026-01-01',
            count: 2,
        );

        $this->database->assertTableHasRow(
            table: 'visits_per_year',
            date: '2025-01-01',
            count: 1,
        );
    }

    #[Test]
    public function replay_test(): void
    {
        $this->database->reset();

        $this->triggerVisit('2026-01-10');
        $this->triggerVisit('2026-01-10');
        $this->triggerVisit('2026-01-10');

        $this->console->call(sprintf('replay "%s" --force', VisitsPerYearProjector::class))->succeeds();

        $this->database->assertTableHasRow(
            table: 'visits_per_year',
            date: '2026-01-01',
            count: 3,
        );
    }
}

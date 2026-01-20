<?php

namespace Tests\Analytics\VisitsPerYear;

use App\Analytics\VisitsPerYear\VisitsPerYearProjector;
use PHPUnit\Framework\Attributes\Test;
use Tests\Analytics\TestsAnalytics;
use Tests\IntegrationTestCase;

class VisitsPerYearProjectorTest extends IntegrationTestCase
{
    use TestsAnalytics;

    #[Test]
    public function events_are_persisted(): void
    {
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
        $this->triggerVisit('2026-01-10');
        $this->triggerVisit('2026-01-10');
        $this->triggerVisit('2026-01-10');

        $this->console->call(sprintf('replay "%s" --force', VisitsPerYearProjector::class))->assertSuccess();

        $this->database->assertTableHasRow(
            table: 'visits_per_year',
            date: '2026-01-01',
            count: 3,
        );
    }
}

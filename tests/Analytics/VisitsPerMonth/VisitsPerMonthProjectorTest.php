<?php

namespace Tests\Analytics\VisitsPerMonth;

use App\Analytics\VisitsPerMonth\VisitsPerMonthProjector;
use PHPUnit\Framework\Attributes\Test;
use Tests\Analytics\TestsAnalytics;
use Tests\IntegrationTestCase;

class VisitsPerMonthProjectorTest extends IntegrationTestCase
{
    use TestsAnalytics;

    #[Test]
    public function events_are_persisted(): void
    {
        $this->triggerVisit('2026-01-05');

        $this->database->assertTableHasRow(
            table: 'visits_per_month',
            date: '2026-01-01',
            count: 1,
        );

        $this->triggerVisit('2026-01-10');
        $this->triggerVisit('2026-02-10');

        $this->database->assertTableHasRow(
            table: 'visits_per_month',
            date: '2026-01-01',
            count: 2,
        );

        $this->database->assertTableHasRow(
            table: 'visits_per_month',
            date: '2026-02-01',
            count: 1,
        );
    }

    #[Test]
    public function replay_test(): void
    {
        $this->triggerVisit('2026-01-10');
        $this->triggerVisit('2026-01-10');
        $this->triggerVisit('2026-01-10');

        $this->console->call(sprintf('replay "%s" --force', VisitsPerMonthProjector::class))->assertSuccess();

        $this->database->assertTableHasRow(
            table: 'visits_per_month',
            date: '2026-01-01',
            count: 3,
        );
    }
}

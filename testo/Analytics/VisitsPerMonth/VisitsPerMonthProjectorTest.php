<?php

declare(strict_types=1);

namespace Tests\Testo\Analytics\VisitsPerMonth;

use App\Analytics\VisitsPerMonth\VisitsPerMonthProjector;
use Testo\Test;
use Tests\Testo\Analytics\TestsAnalytics;
use Tests\Testo\Support\IntegrationTestCase;

#[Test]
class VisitsPerMonthProjectorTest extends IntegrationTestCase
{
    use TestsAnalytics;

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

    public function replay_test(): void
    {
        $this->triggerVisit('2026-01-10');
        $this->triggerVisit('2026-01-10');
        $this->triggerVisit('2026-01-10');

        $this->console->call(sprintf('replay "%s" --force', VisitsPerMonthProjector::class))->succeeds();

        $this->database->assertTableHasRow(
            table: 'visits_per_month',
            date: '2026-01-01',
            count: 3,
        );
    }
}

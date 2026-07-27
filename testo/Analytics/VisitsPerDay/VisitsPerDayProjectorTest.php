<?php

declare(strict_types=1);

namespace Tests\Testo\Analytics\VisitsPerDay;

use App\Analytics\VisitsPerDay\VisitsPerDayProjector;
use Testo\Test;
use Tests\Testo\Analytics\TestsAnalytics;
use Tests\Testo\Support\IntegrationTestCase;

#[Test]
class VisitsPerDayProjectorTest extends IntegrationTestCase
{
    use TestsAnalytics;

    public function events_are_persisted(): void
    {
        $this->triggerVisit('2026-01-01');

        $this->database->assertTableHasRow(
            table: 'visits_per_day',
            date: '2026-01-01 00:00:00',
            count: 1,
        );

        $this->triggerVisit('2026-01-01');
        $this->triggerVisit('2026-01-02');

        $this->database->assertTableHasRow(
            table: 'visits_per_day',
            date: '2026-01-01 00:00:00',
            count: 2,
        );

        $this->database->assertTableHasRow(
            table: 'visits_per_day',
            date: '2026-01-02 00:00:00',
            count: 1,
        );
    }

    public function replay_test(): void
    {
        $this->triggerVisit();
        $this->triggerVisit();
        $this->triggerVisit();

        $this->console->call(sprintf('replay "%s" --force', VisitsPerDayProjector::class))->succeeds();

        $this->database->assertTableHasRow(
            table: 'visits_per_day',
            date: '2026-01-01 00:00:00',
            count: 3,
        );
    }
}

<?php

declare(strict_types=1);

namespace Tests\Testo\Analytics\VisitsPerHour;

use App\Analytics\VisitsPerHour\VisitsPerHourProjector;
use Testo\Test;
use Tests\Testo\Analytics\TestsAnalytics;
use Tests\Testo\Support\IntegrationTestCase;

#[Test]
class VisitsPerHourProjectorTest extends IntegrationTestCase
{
    use TestsAnalytics;

    public function events_are_persisted(): void
    {
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

    public function replay_test(): void
    {
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

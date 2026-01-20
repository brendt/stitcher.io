<?php

namespace Tests\Analytics\VisitsPerHour;

use App\Analytics\VisitsPerHour\VisitsPerHourProjector;
use PHPUnit\Framework\Attributes\Test;
use Tests\Analytics\TestsAnalytics;
use Tests\IntegrationTestCase;

class VisitsPerHourProjectorTest extends IntegrationTestCase
{
    use TestsAnalytics;

    #[Test]
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

    #[Test]
    public function replay_test(): void
    {
        $this->triggerVisit('2026-01-01 01:00:00');
        $this->triggerVisit('2026-01-01 01:00:00');
        $this->triggerVisit('2026-01-01 01:00:00');

        $this->console->call(sprintf('replay "%s" --force', VisitsPerHourProjector::class))->assertSuccess();

        $this->database->assertTableHasRow(
            table: 'visits_per_hour',
            hour: '2026-01-01 01:00:00',
            count: 3,
        );
    }
}

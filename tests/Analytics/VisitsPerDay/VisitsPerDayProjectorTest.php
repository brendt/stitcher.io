<?php

namespace Tests\Analytics\VisitsPerDay;

use App\Analytics\VisitsPerDay\VisitsPerDay;
use App\Analytics\VisitsPerDay\VisitsPerDayProjector;
use PHPUnit\Framework\Attributes\Test;
use Tests\Analytics\TestsAnalytics;
use Tests\IntegrationTestCase;
use function Tempest\Database\query;

class VisitsPerDayProjectorTest extends IntegrationTestCase
{
    use TestsAnalytics;

    #[Test]
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

    #[Test]
    public function replay_test(): void
    {
        $this->triggerVisit();
        $this->triggerVisit();
        $this->triggerVisit();

        $this->console->call(sprintf('replay "%s" --force', VisitsPerDayProjector::class))->assertSuccess();

        $this->database->assertTableHasRow(
            table: 'visits_per_day',
            date: '2026-01-01 00:00:00',
            count: 3,
        );
    }
}

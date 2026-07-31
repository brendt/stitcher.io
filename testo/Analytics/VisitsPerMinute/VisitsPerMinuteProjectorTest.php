<?php

declare(strict_types=1);

namespace Tests\Testo\Analytics\VisitsPerMinute;

use App\Analytics\VisitsPerMinute\VisitsPerMinuteProjector;
use Testo\Test;
use Tests\Testo\Analytics\TestsAnalytics;
use Tests\Testo\Support\IntegrationTestCase;

#[Test]
class VisitsPerMinuteProjectorTest extends IntegrationTestCase
{
    use TestsAnalytics;

    public function events_are_persisted(): void
    {
        $this->triggerVisit('2026-01-01 10:10:40');

        $this->database->assertTableHasRow(
            table: 'visits_per_minute',
            time: '2026-01-01 10:10:00',
            count: 1,
        );

        $this->triggerVisit('2026-01-01 10:10:30');
        $this->triggerVisit('2026-01-01 10:11:30');

        $this->database->assertTableHasRow(
            table: 'visits_per_minute',
            time: '2026-01-01 10:10:00',
            count: 2,
        );

        $this->database->assertTableHasRow(
            table: 'visits_per_minute',
            time: '2026-01-01 10:11:00',
            count: 1,
        );
    }

    public function replay_test(): void
    {
        $this->triggerVisit('2026-01-01 10:10:40');
        $this->triggerVisit('2026-01-01 10:10:41');
        $this->triggerVisit('2026-01-01 10:10:42');

        $this->console->call(sprintf('replay "%s" --force', VisitsPerMinuteProjector::class))->succeeds();

        $this->database->assertTableHasRow(
            table: 'visits_per_minute',
            time: '2026-01-01 10:10:00',
            count: 3,
        );
    }
}

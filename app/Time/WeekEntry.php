<?php

namespace App\Time;

use Tempest\DateTime\DateTime;
use Tempest\DateTime\Timezone;

final class WeekEntry
{
    public function __construct(
        public string $week,
        /** @var TimeEntry[] */
        public array $timeEntries = [],
    ) {}

    public float $totalHours {
        get => array_sum(array_map(
            fn (TimeEntry $entry) => $entry->totalHours,
            $this->timeEntries,
        ));
    }

    public bool $isCurrent {
        get => $this->week === DateTime::now(Timezone::EUROPE_BRUSSELS)->format('yyyy-ww');
    }

    /** self[] */
    public static function fromTimeEntries(TimeEntry ...$entries): array
    {
        $perWeek = [];

        foreach ($entries as $entry) {
            $week = $entry->start->format('yyyy-ww');

            $perWeek[$week] ??= new WeekEntry($week);
            $perWeek[$week]->timeEntries[] = $entry;
        }

        return $perWeek;
    }
}

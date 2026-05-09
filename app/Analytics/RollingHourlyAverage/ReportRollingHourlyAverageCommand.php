<?php

namespace App\Analytics\RollingHourlyAverage;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;
use Tempest\DateTime\DateTime;
use function Tempest\Database\query;

final class ReportRollingHourlyAverageCommand
{
    use HasConsole;

    #[ConsoleCommand(name: 'report:rolling-hourly-average'), Schedule(Every::HOUR)]
    public function __invoke(
        ?string $from = null,
        ?string $to = null,
    ): void
    {
        $currentTime = DateTime::parse($from ?? 'now');
        $to = DateTime::parse($to ?? 'now');

        while ($currentTime <= $to) {
            $average = $this->reportAverage($currentTime);

            $this->success($currentTime->format('YYYY-MM-dd HH:00') . " {$average}");

            $currentTime = $currentTime->plusHour();
        }
    }

    private function reportAverage(DateTime $currentHour): int
    {
        $average = query('visits_per_hour')
            ->select('AVG(count) AS `avg`')
            ->where('hour > ?', $currentHour->minusHours(12))
            ->where('hour <= ?', $currentHour)
            ->first()['avg'] ?? 0;

        $averageForThisHour = (int)round($average);

        $existingHour = query('rolling_hourly_average')
            ->select()
            ->where('date = ?', $currentHour)
            ->first();

        if ($existingHour) {
            query('rolling_hourly_average')
                ->update(count: $averageForThisHour)
                ->where('date = ?', $currentHour)
                ->execute();
        } else {
            query('rolling_hourly_average')
                ->insert(count: $averageForThisHour, date: $currentHour)
                ->execute();
        }

        return $averageForThisHour;
    }
}
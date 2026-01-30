<?php

namespace App\Analytics\RollingDailyAverage;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;
use Tempest\DateTime\DateTime;
use function Tempest\Database\query;

final class ReportRollingDailyAverageCommand
{
    use HasConsole;

    #[ConsoleCommand(name: 'report:rolling-daily-average'), Schedule(Every::HOUR)]
    public function __invoke(
        ?string $from = null,
        ?string $to = null,
    ): void
    {
        $currentDay = DateTime::parse($from ?? 'now');
        $to = DateTime::parse($to ?? 'now');

        while ($currentDay <= $to) {
            $average = $this->reportAverage($currentDay);

            $this->success($currentDay->format('YYYY-MM-dd') . " {$average}");

            $currentDay = $currentDay->plusDay();
        }
    }

    private function reportAverage(DateTime $currentDay): int
    {
        $average = query('visits_per_day')
            ->select('AVG(count) AS `avg`')
            ->where('date > ?', $currentDay->minusDays(14))
            ->where('date <= ?', $currentDay)
            ->first()['avg'] ?? 0;

        $averageForThisDay = (int)round($average);

        $existingDay = query('rolling_daily_average')
            ->select()
            ->where('date = ?', $currentDay)
            ->first();

        if ($existingDay) {
            query('rolling_daily_average')
                ->update(count: $averageForThisDay)
                ->where('date = ?', $currentDay)
                ->execute();
        } else {
            query('rolling_daily_average')
                ->insert(count: $averageForThisDay, date: $currentDay)
                ->execute();
        }

        return $averageForThisDay;
    }
}
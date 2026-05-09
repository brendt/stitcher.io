<?php

namespace App\Analytics\RollingMonthlyAverage;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;
use Tempest\DateTime\DateTime;
use function Tempest\Database\query;

final class ReportRollingMonthlyAverageCommand
{
    use HasConsole;

    #[ConsoleCommand(name: 'report:rolling-monthly-average'), Schedule(Every::HOUR)]
    public function __invoke(
        ?string $from = null,
        ?string $to = null,
    ): void
    {
        $currentMonth = DateTime::parse($from ?? 'now');
        $to = DateTime::parse($to ?? 'now');

        while ($currentMonth <= $to) {
            $average = $this->reportAverage($currentMonth);

            $this->success($currentMonth->format('YYYY-MM') . " {$average}");

            $currentMonth = $currentMonth->plusMonth();
        }
    }

    private function reportAverage(DateTime $currentMonth): int
    {
        $average = query('visits_per_month')
            ->select('AVG(count) AS `avg`')
            ->where('date > ?', $currentMonth->minusMonths(6))
            ->where('date <= ?', $currentMonth)
            ->first()['avg'] ?? 0;

        $averageForThisMonth = (int)round($average);

        $existingMonth = query('rolling_monthly_average')
            ->select()
            ->where('date = ?', $currentMonth)
            ->first();

        if ($existingMonth) {
            query('rolling_monthly_average')
                ->update(count: $averageForThisMonth)
                ->where('date = ?', $currentMonth)
                ->execute();
        } else {
            query('rolling_monthly_average')
                ->insert(count: $averageForThisMonth, date: $currentMonth)
                ->execute();
        }

        return $averageForThisMonth;
    }
}
<?php

namespace App\Analytics\DailyAveragePerMonth;

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
        $day = DateTime::parse($from ?? 'now');
        $to = DateTime::parse($to ?? 'now');

        while ($day <= $to) {
            $startOfMonth = $day->startOfMonth();
            $endOfMonth = $day->endOfMonth();

            $diff = $endOfMonth->getTimestamp()->getSeconds() - $startOfMonth->getTimestamp()->getSeconds();
            $amountOfDays = (int)round($diff / (60 * 60 * 24));

            $average = $this->reportAverage($amountOfDays, $startOfMonth, $endOfMonth, $day);

            $this->success($day->format('YYYY-MM') . " {$average}");

            $day = $day->plusMonth();
        }
    }

    private function reportAverage(
        int $amountOfDays,
        DateTime $start,
        DateTime $end,
        DateTime $currentMonth,
    ): int
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
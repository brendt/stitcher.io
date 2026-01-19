<?php

namespace App\Analytics;

use App\Analytics\VisitsPerDay\VisitsPerDay;
use App\Analytics\VisitsPerHour\VisitsPerHour;
use App\Analytics\VisitsPerMonth\VisitsPerMonth;
use App\Analytics\VisitsPerPostPerWeek\VisitsPerPostPerWeek;
use App\Analytics\VisitsPerYear\VisitsPerYear;
use Tempest\DateTime\DateTime;
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\Database\query;
use function Tempest\Support\arr;
use function Tempest\View\view;

final class AnalyticsController
{
    #[Get('/analytics')]
    public function __invoke(): View
    {
        $visitsPerHour = new Chart(arr(query(VisitsPerHour::class)
            ->select()
            ->orderBy('hour DESC')
            ->limit(48)
            ->all())->reverse());

        $visitsPerDay = new Chart(arr(query(VisitsPerDay::class)
            ->select()
            ->orderBy('date DESC')
            ->limit(62)
            ->all())->reverse());

        $visitsPerMonth = new Chart(arr(query(VisitsPerMonth::class)
            ->select()
            ->orderBy('date DESC')
            ->limit(36)
            ->all())->reverse());

        $visitsPerYear = new Chart(arr(query(VisitsPerYear::class)
            ->select()
            ->orderBy('date ASC')
            ->all()
        ));

        $date = DateTime::now()->minusDays(14);

        $popularPosts = query(VisitsPerPostPerWeek::class)
            ->select()
            ->where('date > ?', $date)
            ->whereNot('uri', '/')
            ->orderBy('count DESC')
            ->limit(8)
            ->all();

        return view(
            'analytics.view.php',
            visitsPerHour: $visitsPerHour,
            visitsPerDay: $visitsPerDay,
            visitsPerMonth: $visitsPerMonth,
            visitsPerYear: $visitsPerYear,
            popularPosts: $popularPosts,
        );
    }
}
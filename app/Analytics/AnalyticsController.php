<?php

namespace App\Analytics;

use App\Analytics\VisitsPerDay\VisitsPerDay;
use App\Analytics\VisitsPerHour\VisitsPerHour;
use App\Analytics\VisitsPerMinute\VisitsPerMinute;
use App\Analytics\VisitsPerMonth\VisitsPerMonth;
use App\Analytics\VisitsPerPostPerDay\VisitsPerPostPerDay;
use App\Analytics\VisitsPerPostPerWeek\VisitsPerPostPerWeek;
use App\Analytics\VisitsPerYear\VisitsPerYear;
use Tempest\DateTime\DateTime;
use Tempest\Router\Get;
use Tempest\Router\StaticPage;
use Tempest\View\View;
use function Tempest\Database\query;
use function Tempest\Support\arr;
use function Tempest\View\view;

final class AnalyticsController
{
    #[Get('/analytics'), StaticPage]
    public function __invoke(): View
    {
        $visitsThisDay = query(VisitsPerDay::class)
            ->select()
            ->orderBy('date DESC')
            ->limit(1)
            ->first();

        $visitsThisMonth = query(VisitsPerMonth::class)
            ->select()
            ->orderBy('date DESC')
            ->limit(1)
            ->first();

        $mostPopularPostToday = query(VisitsPerPostPerDay::class)
            ->select()
            ->where('date = ?', DateTime::now()->startOfDay())
            ->where('uri LIKE ?', '/blog/%')
            ->where('uri NOT LIKE ?', '/blog/%/comments')
            ->orderBy('count DESC')
            ->limit(1)
            ->first();

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

        $popularPosts = query(VisitsPerPostPerWeek::class)
            ->select('uri', 'SUM(count) AS count')
            ->where('date > ?', DateTime::now()->minusDays(32)->startOfDay())
            ->where('uri LIKE ?', '/blog/%')
            ->where('uri NOT LIKE ?', '/blog/%/comments')
            ->orderBy('SUM(count) DESC')
            ->groupBy('uri')
            ->limit(8)
            ->all();

        return view(
            'analytics.view.php',
            realtimeVisitCount: $this->realtimeVisitCount(),
            visitsThisDay: $visitsThisDay?->count ?? 0,
            visitsThisMonth: $visitsThisMonth?->count ?? 0,
            mostPopularPostToday: $mostPopularPostToday ?? null,
            visitsPerHour: $visitsPerHour,
            visitsPerDay: $visitsPerDay,
            visitsPerMonth: $visitsPerMonth,
            visitsPerYear: $visitsPerYear,
            popularPosts: $popularPosts,
        );
    }

    #[Get('/analytics/realtime')]
    public function realtime(): View
    {
        return view('x-realtime.view.php', visits: $this->realtimeVisitCount());
    }

    private function realtimeVisitCount(): int
    {
        $time = DateTime::parse(DateTime::now()->minusMinutes(4)->format('yyyy-MM-dd HH:mm') . ':00');

        return query(VisitsPerMinute::class)
            ->select()
            ->where('time > ?', $time)
            ->first()
            ?->count ?? 0;
    }
}
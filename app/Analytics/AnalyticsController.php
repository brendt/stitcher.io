<?php

namespace App\Analytics;

use App\Analytics\RollingDailyAverage\RollingDailyAverage;
use App\Analytics\RollingHourlyAverage\RollingHourlyAverage;
use App\Analytics\RollingMonthlyAverage\RollingMonthlyAverage;
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
    public function analytics(): View
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

        $visitsPerHour = new Chart(
            datasets: arr([
                new Dataset(
                    title: 'Visits per hour',
                    entries: arr(query(VisitsPerHour::class)
                        ->select()
                        ->orderBy('hour DESC')
                        ->where('hour > ?', DateTime::now()->minusHours(48))
                        ->all())->reverse(),
                ),
                new Dataset(
                    title: 'Rolling 12-hour average',
                    entries: arr(query(RollingHourlyAverage::class)
                        ->select()
                        ->orderBy('date DESC')
                        ->where('date > ?', DateTime::now()->minusHours(48))
                        ->all())->reverse(),
                    color: '#DE2F7844',
                    pointStyle: 'cross',
                ),
            ]),
        );

        $visitsPerDay = new Chart(
            datasets: arr([
                new Dataset(
                    title: 'Visits per day',
                    entries: arr(query(VisitsPerDay::class)
                        ->select()
                        ->orderBy('date DESC')
                        ->where('date > ?', DateTime::now()->minusDays(62))
                        ->all())->reverse(),
                ),
                new Dataset(
                    title: 'Rolling 14-day average',
                    entries: arr(query(RollingDailyAverage::class)
                        ->select()
                        ->orderBy('date DESC')
                        ->where('date > ?', DateTime::now()->minusDays(62))
                        ->all())->reverse(),
                    color: '#DE2F7844',
                    pointStyle: 'cross',
                ),
            ]),
        );

        $visitsPerMonth = new Chart(
            datasets: arr([
                new Dataset(
                    title: 'Visits per month',
                    entries: arr(query(VisitsPerMonth::class)
                        ->select()
                        ->orderBy('date DESC')
                        ->where('date > ?', DateTime::now()->minusMonths(36))
                        ->all())->reverse(),
                ),
                new Dataset(
                    title: 'Rolling 6-month average',
                    entries: arr(query(RollingMonthlyAverage::class)
                        ->select()
                        ->orderBy('date DESC')
                        ->where('date > ?', DateTime::now()->minusMonths(36))
                        ->all())->reverse(),
                    color: '#DE2F7844',
                    pointStyle: 'cross',
                ),
            ]),
        );

        $visitsPerYear = Chart::forData([
            'Visits per year' => arr(query(VisitsPerYear::class)
                ->select()
                ->orderBy('date ASC')
                ->all(),
            ),
        ]);

        $popularPosts = query(VisitsPerPostPerWeek::class)
            ->select('uri', 'SUM(count) AS count')
            ->where('date > ?', DateTime::now()->minusDays(31)->startOfDay())
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

    #[Get('/analytics/post/{uri:.*}')]
    public function forPost(string $uri): View
    {
        $uri = '/' . $uri;

        $visitsPerDay = Chart::forData([
            'Visits last 124 days' => arr(query(VisitsPerPostPerDay::class)
                ->select()
                ->orderBy('date DESC')
                ->where('date > ?', DateTime::now()->minusDays(124))
                ->where('uri', $uri)
                ->all())->reverse()
        ]);

        return view(
            'analytics-post.view.php',
            uri: $uri,
            visitsPerDay: $visitsPerDay,
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
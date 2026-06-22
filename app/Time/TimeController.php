<?php

namespace App\Time;

use App\Support\Authentication\Admin;
use Tempest\DateTime\DateTime;
use Tempest\Http\Response;
use Tempest\Http\Responses\Forbidden;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\Prefix;
use Tempest\View\View;
use function Tempest\View\view;

#[Prefix('/time'), Admin]
final readonly class TimeController
{
    #[Get('/')]
    public function index(): View
    {
        $entries = TimeEntry::select()
            ->orderBy('start DESC')
            ->all();

        $perWeek = WeekEntry::fromTimeEntries(...$entries);
        $isRunning = TimeEntry::select()->whereNull('end')->first() !== null;

        return view('time.view.php', perWeek: $perWeek, isRunning: $isRunning);
    }

    #[Post('/start')]
    public function start(): View|Response
    {
        $existing = TimeEntry::select()->whereNull('end')->first();

        if ($existing) {
            return new Forbidden();
        }

        TimeEntry::create(
            start: DateTime::now(),
        );

        return $this->render();
    }

    #[Post('/stop')]
    public function stop(): View|Response
    {
        $existing = TimeEntry::select()->whereNull('end')->first();

        if (! $existing) {
            return new Forbidden();
        }

        $existing->update(
            end: DateTime::now(),
        );

        return $this->render();
    }

    private function render(): View
    {
        $entries = TimeEntry::select()
            ->orderBy('start DESC')
            ->all();

        $perWeek = WeekEntry::fromTimeEntries(...$entries);
        $isRunning = TimeEntry::select()->whereNull('end')->first() !== null;

        return view('x-time-entries.view.php', perWeek: $perWeek, isRunning: $isRunning);
    }
}
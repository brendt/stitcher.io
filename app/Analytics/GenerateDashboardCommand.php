<?php

namespace App\Analytics;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;

final class GenerateDashboardCommand
{
    use HasConsole;

    #[ConsoleCommand, Schedule(Every::MINUTE)]
    public function __invoke(): void
    {
        $this->console->call('static:generate /analytics');
    }
}
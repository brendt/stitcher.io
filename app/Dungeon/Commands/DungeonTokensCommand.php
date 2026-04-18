<?php

namespace App\Dungeon\Commands;

use App\Dungeon\Dungeon;
use App\Dungeon\Persistence\DungeonUserStats;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;

final class DungeonTokensCommand
{
    use HasConsole;

    #[ConsoleCommand, Schedule(Every::DAY)]
    public function __invoke(): void
    {
        $stats = DungeonUserStats::select()
            ->where('campaignId', Dungeon::CURRENT_CAMPAIGN)
            ->where('tokens < ?', 10)
            ->all();

        foreach ($stats as $stat) {
            if ($stat->tokens < 5) {
                $stat->tokens += 5;
            } elseif ($stat->tokens < 10) {
                $stat->tokens = 10;
            }

            $stat->save();

            $this->info("Added tokens to user {$stat->userId}");
        }

        $this->success('Done');
    }
}
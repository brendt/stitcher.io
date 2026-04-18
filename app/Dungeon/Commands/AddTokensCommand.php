<?php

namespace App\Dungeon\Commands;

use App\Dungeon\Dungeon;
use App\Dungeon\Persistence\DungeonUserStats;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;

final class AddTokensCommand
{
    use HasConsole;

    #[ConsoleCommand(name: 'dungeon:tokens'), Schedule(Every::DAY)]
    public function __invoke(): void
    {
        $stats = DungeonUserStats::select()
            ->where('campaignId', Dungeon::CURRENT_CAMPAIGN)
            ->where('tokens < ?', 10)
            ->all();

        foreach ($stats as $stat) {
            $stat->tokens += 5;
            $stat->save();
            $this->info("Added 5 tokens to user {$stat->userId}");
        }

        $this->success('Done');
    }
}
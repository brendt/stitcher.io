<?php

namespace App\Dungeon\Commands;

use App\Dungeon\Events\UserShopInitialized;
use App\Support\Authentication\User;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;
use function Tempest\EventBus\event;

final class RefreshShopCommand
{
    use HasConsole;

    #[ConsoleCommand(name: 'dungeon:shop'), Schedule(Every::DAY)]
    public function __invoke(int $userId): void
    {
        event(new UserShopInitialized(User::findById($userId)));

        $this->info('Done');
    }
}
<?php

namespace App\Mail;

use App\Mail\Models\Campaign;
use Tempest\Clock\Clock;
use Tempest\CommandBus\CommandBus;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

use function Tempest\Database\query;

final readonly class MailStartCommand
{
    use HasConsole;

    public function __construct(
        private CommandBus $commandBus,
        private Clock $clock,
    ) {}

    #[ConsoleCommand]
    public function __invoke(string $slug): void
    {
        $path = __DIR__ . '/Content/' . $slug . '.md';

        if (! file_exists($path)) {
            $this->error('Mail campaign not found');

            return;
        }

        $exists = query(Campaign::class)
            ->count()
            ->where('path', $path)
            ->execute() > 0;

        if ($exists) {
            $this->error('Mail campaign already exists');

            return;
        }

        $campaign = Campaign::create(
            path: $path,
            startedAt: $this->clock->now(),
        );

        $this->commandBus->dispatch(new StartMailCampaign($campaign->id));

        $this->success('Mail campaign started');
    }
}

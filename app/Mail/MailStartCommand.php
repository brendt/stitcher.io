<?php

namespace App\Mail;

use App\Mail\Models\OutboxCampaign;
use Tempest\CommandBus\CommandBus;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

use function Tempest\Database\query;

final readonly class MailStartCommand
{
    use HasConsole;

    public function __construct(
        private CommandBus $commandBus,
    ) {}

    #[ConsoleCommand]
    public function __invoke(string $slug): void
    {
        $path = __DIR__ . '/Content/' . $slug . '.md';

        if (! file_exists($path)) {
            $this->error('Mail campaign not found');

            return;
        }

        $exists = query(OutboxCampaign::class)
            ->count()
            ->where('path', $path)
            ->execute() > 0;

        if ($exists) {
            $this->error('Mail campaign already exists');

            return;
        }

        $this->commandBus->dispatch(new StartMailCampaign($path));

        $this->success('Mail campaign started');
    }
}

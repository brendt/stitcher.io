<?php

declare(strict_types=1);

namespace App\Support;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final readonly class DeployCommand
{
    use HasConsole;

    #[ConsoleCommand('deploy')]
    public function __invoke(bool $backend = false): void
    {
        $this->info('Starting deploy');

        $this->info('Pulling changes');
        passthru("ssh forge@stitcher.io 'cd stitcher.io && git fetch origin && git reset --hard origin/main'");
        $this->success('Done');

        $deployScript = match (true) {
            $backend => 'deploy-backend.sh',
            $code => 'deploy-code.sh',
            default => 'deploy-full.sh',
        };

        $this->info("Running deploy script `{$deployScript}`");
        passthru("ssh forge@stitcher.io 'cd stitcher.io && bash app/Deploy/{$deployScript}'");

        $this->success('Deploy success');
    }
}

<?php

declare(strict_types=1);

namespace App\Support;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final readonly class DeployCommand
{
    use HasConsole;

    #[ConsoleCommand('deploy')]
    public function __invoke(bool $code = false): void
    {
        $this->info('Starting deploy');

        $this->info('Pulling changes');
        passthru("ssh forge@stitcher.io 'cd stitcher.io && git fetch origin && git reset --hard origin/main'");
        $this->success('Done');

        if ($code === false) {
            $this->info('Running deploy script');
            passthru("ssh forge@stitcher.io 'cd stitcher.io && bash deploy.sh'");
        }

        $this->success('Deploy success');
    }
}

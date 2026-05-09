<?php

namespace App\Aggregate\Posts\Actions;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final class TitleResolveCommand
{
    use HasConsole;

    public function __construct(
        private readonly ResolveTitle $resolveTitle
    ) {}

    #[ConsoleCommand]
    public function __invoke(string $uri): void
    {
        $this->success(($this->resolveTitle)($uri));
    }
}
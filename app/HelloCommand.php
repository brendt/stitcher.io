<?php

namespace App;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;

final class HelloCommand
{
    public function __construct(
        private readonly Console $console,
    ) {}

    #[ConsoleCommand]
    public function world(string $name = 'stranger'): void
    {
        $this->console->success("Hello, {$name}!");
    }
}

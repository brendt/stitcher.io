<?php

namespace App\Mail;

use Tempest\CommandBus\Async;

#[Async]
final readonly class StartMailCampaign
{
    public function __construct(
        public string $path,
    ) {}
}

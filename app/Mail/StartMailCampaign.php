<?php

namespace App\Mail;

use Tempest\CommandBus\Async;
use Tempest\Database\PrimaryKey;

#[Async]
final readonly class StartMailCampaign
{
    public function __construct(
        public PrimaryKey $campaignId,
    ) {}
}

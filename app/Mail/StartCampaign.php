<?php

namespace App\Mail;

final readonly class StartCampaign
{
    public function __construct(
        public string $path,
    ) {}
}

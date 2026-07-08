<?php

namespace App\Mail;

final readonly class StartCampaign
{
    public function __construct(public string $path)
    {
        if (! is_file($path)) {
            throw new InvalidCampaign("File `{$path}` does not exist");
        }

        if (! str_ends_with($path, '.md')) {
            throw new InvalidCampaign("File should be markdown: `{$path}`");
        }
    }
}
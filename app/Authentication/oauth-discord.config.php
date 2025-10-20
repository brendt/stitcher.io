<?php

use Tempest\Auth\OAuth\Config\DiscordOAuthConfig;
use function Tempest\env;

return new DiscordOAuthConfig(
    clientId: env('DISCORD_CLIENT_ID'),
    clientSecret: env('DISCORD_CLIENT_SECRET'),
    redirectTo: '/auth/discord',
    tag: 'discord',
);
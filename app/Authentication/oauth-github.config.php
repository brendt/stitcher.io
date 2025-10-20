<?php

use Tempest\Auth\OAuth\Config\GitHubOAuthConfig;
use function Tempest\env;

return new GitHubOAuthConfig(
    clientId: env('GITHUB_CLIENT_ID'),
    clientSecret: env('GITHUB_CLIENT_SECRET'),
    redirectTo: '/auth/github',
    tag: 'github',
);
<?php

use App\Authentication\AuthController;
use Tempest\Auth\OAuth\Config\GoogleOAuthConfig;
use function Tempest\env;

return new GoogleOAuthConfig(
    clientId: env('GOOGLE_CLIENT_ID'),
    clientSecret: env('GOOGLE_CLIENT_SECRET'),
    redirectTo: '/auth/google',
    tag: 'google',
);
<?php

namespace App\Support\Authentication;

use Tempest\Auth\Authentication\Authenticator;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Back;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;

#[SkipDiscovery]
final readonly class AuthMiddleware implements HttpMiddleware
{
    public function __construct(
        private Authenticator $authenticator,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        if ($this->authenticator->current() === null) {
            return new Back();
        }

        return $next($request);
    }
}
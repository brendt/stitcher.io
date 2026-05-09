<?php

namespace App\Dungeon\Http;

use Tempest\Auth\Authentication\Authenticator;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use function Tempest\Router\uri;

#[SkipDiscovery]
final readonly class DungeonAuthMiddleware implements HttpMiddleware
{
    public function __construct(
        private Authenticator $auth,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        if ($this->auth->current() === null) {
            return new Redirect(uri([DungeonAuthController::class, 'login']));
        }

        return $next($request);
    }
}
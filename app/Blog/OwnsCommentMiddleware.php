<?php

namespace App\Blog;

use Tempest\Auth\Authentication\Authenticator;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Forbidden;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Router\MatchedRoute;

final readonly class OwnsCommentMiddleware implements HttpMiddleware
{
    public function __construct(
        private Authenticator $authenticator,
        private MatchedRoute $route,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $user = $this->authenticator->current();

        if (! $user) {
            return new Forbidden();
        }

        ld($this->route);
    }
}
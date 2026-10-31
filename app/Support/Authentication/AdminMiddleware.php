<?php

namespace App\Support\Authentication;

use Tempest\Auth\Authentication\Authenticator;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Session\Session;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;

use function Tempest\Router\uri;

#[SkipDiscovery]
final readonly class AdminMiddleware implements HttpMiddleware
{
    public function __construct(
        private Authenticator $authenticator,
        private Session $session,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $user = $this->authenticator->current();

        if (! $user instanceof User) {
            $query = http_build_query($request->query);

            $this->session->set('back', $query === '' ? $request->path : "{$request->path}?{$query}");

            return new Redirect(uri([AuthController::class, 'auth']));
        }

        if (! $user->isAdmin) {
            return new Redirect('/');
        }

        return $next($request);
    }
}

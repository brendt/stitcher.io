<?php

namespace App\Support\Authentication;

use Tempest\Auth\Authentication\Authenticatable;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Auth\OAuth\OAuthClient;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Database\PrimaryKey;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Back;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Session\Session;
use Tempest\Router\Get;
use function Tempest\env;

final readonly class AuthController
{
    public function __construct(
        private Authenticator $authenticator,
        private AppConfig $appConfig,
        private Container $container,
        private Session $session,
    ) {}

    #[Get('/logout')]
    public function logout(Authenticator $authenticator): Response
    {
        $authenticator->deauthenticate();

        return new Back();
    }

    #[Get('/login')]
    #[Get('/auth/{?type}')]
    public function auth(?string $type, Request $request): Response
    {
        if ($response = $this->autoLogin()) {
            return $response;
        }

        $type ??= 'google';

        $oauth = $this->container->get(OAuthClient::class, tag: $type);

        $code = $request->get('code');

        if ($code === null) {
            $this->session->set('back', $request->get('back'));

            return $oauth->createRedirect();
        }

        $oauth->authenticate($request, function (OAuthUser $oauthUser): Authenticatable {
            $user = User::select()
                ->where('email = ?', $oauthUser->email)
                ->first();

            if (! $user) {
                $user = User::create(
                    email: $oauthUser->email,
                    name: $oauthUser->name ?? $oauthUser->nickname,
                    role: Role::USER,
                );
            } else {
                $user->name = $oauthUser->name ?? $oauthUser->nickname;
                $user->save();
            }

            return $user;
        });

        $back = $this->session->consume('back', '/');

        return new Redirect($back);
    }

    private function autoLogin(): ?Redirect
    {
        if ($this->appConfig->environment->isLocal() && env('AUTO_LOGIN')) {
            $this->authenticator->authenticate(User::get(new PrimaryKey(1)));

            return new Redirect('/');
        }

        return null;
    }
}
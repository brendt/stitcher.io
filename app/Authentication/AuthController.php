<?php

namespace App\Authentication;

use Tempest\Auth\Authentication\Authenticatable;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Auth\OAuth\OAuthClient;
use Tempest\Auth\OAuth\OAuthUser;
use Tempest\Container\Tag;
use Tempest\Core\AppConfig;
use Tempest\Database\PrimaryKey;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Back;
use Tempest\Router\Get;
use function Tempest\env;

final class AuthController
{
    #[Get('/logout')]
    public function logout(Authenticator $authenticator): Response
    {
        $authenticator->deauthenticate();

        return new Back();
    }

    #[Get('/auth/google')]
    public function google(
        Request $request,
        Authenticator $authenticator,
        AppConfig $appConfig,
        #[Tag('google')] OAuthClient $oauth,
    ): Response {
        if ($appConfig->environment->isLocal() && env('AUTO_LOGIN')) {
            $authenticator->authenticate(User::get(new PrimaryKey(1)));
        }

        if ($authenticator->current()) {
            return new Back();
        }

        $code = $request->get('code');

        if ($code === null) {
            return $oauth->createRedirect();
        }

        $oauth->authenticate($request, function (OAuthUser $oauthUser): Authenticatable {
            $user = User::select()
                ->where('email = ?', $oauthUser->email)
                ->first();

            if (! $user) {
                $user = User::create(
                    email: $oauthUser->email,
                    name: $oauthUser->name,
                    role: Role::USER,
                );
            }

            return $user;
        });

        return new Back();
    }
}
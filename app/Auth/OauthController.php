<?php

namespace App\Auth;

use Google\Client;
use Google\Service\Oauth2;
use Tempest\Http\Get;
use Tempest\Http\Request;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Session\Session;

final readonly class OauthController
{
    public function __construct(private Authenticator $authenticator) {}

    #[Get('/app/oauth')]
    public function __invoke(Request $request, Session $session): Redirect
    {
        $client = new Client();
        $client->setAuthConfig(__DIR__ . '/../stitcher.json');
        $client->setRedirectUri("https://redirectmeto.com/http://stitcher.io.test/app/oauth");
        $client->setScopes([
            Oauth2::USERINFO_EMAIL,
            Oauth2::USERINFO_PROFILE,
        ]);

        $code = $request->get('code');

        if ($code === null) {
            $session->set('back', $request->get('back'));
            return new Redirect($client->createAuthUrl());
        }

        $client->fetchAccessTokenWithAuthCode($code);
        $oauth = new Oauth2($client);
        $userinfo = $oauth->userinfo->get();

        $user = User::query()->where('email = ?', $userinfo->email)->first();

        if (! $user) {
            $user = (new User(
                name: $userinfo->givenName,
                email: $userinfo->email
            ))->save();
        }

        $this->authenticator->login($user);

        return new Redirect($session->consume('back', '/'));
    }

}
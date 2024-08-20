<?php

namespace App\Auth;

use Google\Client;
use Google\Service\Oauth2;
use Tempest\Http\Get;
use Tempest\Http\Request;
use Tempest\Http\Responses\Redirect;

final readonly class OauthController
{
    public function __construct(private Authenticator $authenticator) {}

    #[Get('/app/oauth')]
    public function __invoke(Request $request): Redirect
    {
        $client = new Client();
        $client->setAuthConfig(__DIR__ . '/../stitcher.json');
        $back = $request->get('back') ?? '/';
        $client->setRedirectUri("https://redirectmeto.com/http://stitcher.io.test/app/oauth?back={$back}");
        $client->setScopes([
            Oauth2::USERINFO_EMAIL,
            Oauth2::USERINFO_PROFILE,
        ]);

        $code = $request->get('code');

        if ($code === null) {
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

        return new Redirect('/');
    }

}
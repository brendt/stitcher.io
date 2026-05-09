<?php

namespace App\Dungeon\Http;

use Tempest\Auth\Authentication\Authenticator;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Session\Session;
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\Router\uri;
use function Tempest\View\view;

final class DungeonAuthController
{
    #[Get('/dungeon/login')]
    public function login(Session $session): View
    {
        $session->set('back', uri([DungeonHomeController::class, 'index']));

        return view('dungeon-login.view.php');
    }

    #[Get('/dungeon/logout')]
    public function logout(Authenticator $authenticator): Redirect
    {
        $authenticator->deauthenticate();

        return new Redirect(uri([self::class, 'login']));
    }
}

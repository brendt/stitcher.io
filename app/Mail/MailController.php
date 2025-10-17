<?php

namespace App\Mail;

use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\view;

final class MailController
{
    #[Get('/mail')]
    #[Get('/newsletter/subscribe')]
    public function mail(): View
    {
        return view('mail.view.php');
    }
}
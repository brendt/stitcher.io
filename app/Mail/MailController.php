<?php

namespace App\Mail;

use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\view;

final class MailController
{
    #[Get('/mail')]
    #[Get('/newsletter/subscribe')]
    public function subscribe(): View
    {
        return view('mail-subscribe.view.php');
    }

    #[Get('/mail/archive')]
    public function overview(MailRepository $repository): View
    {
        $mails = $repository->all();

        return view('mail-overview.view.php', mails: $mails);
    }

    #[Get('/mail/archive/{slug}')]
    public function show(string $slug, MailRepository $repository): View
    {
        $mail = $repository->find($slug);

        return view('mail-show.view.php', mail: $mail);
    }

    #[Get('/mail/export/{slug}')]
    public function export(string $slug, MailRepository $repository): View
    {
        $mail = $repository->find($slug);

        return view('mail-export.view.php', mail: $mail);
    }
}
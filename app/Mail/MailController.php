<?php

namespace App\Mail;

use App\Support\Authentication\Admin;
use Tempest\CommandBus\CommandBus;
use Tempest\Http\Response;
use Tempest\Http\Responses\NotFound;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\StaticPage;
use Tempest\View\View;

use function Tempest\View\view;

final class MailController
{
    #[Get('/mail')]
    #[Get('/newsletter/subscribe')]
    public function subscribe(): View
    {
        return view('mail-subscribe.view.php');
    }

    #[Get('/mail/archive')]
    #[StaticPage]
    public function overview(MailRepository $repository): View
    {
        $mails = $repository->all();

        return view('mail-overview.view.php', mails: $mails);
    }

    #[Get('/mail/archive/{slug}')]
    #[StaticPage(MailRepository::class)]
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

    #[Admin, Post('/mail/send/{slug}')]
    public function send(string $slug, MailRepository $repository, CommandBus $commandBus): Response|View
    {
        $mail = $repository->find($slug);

        if (! $mail) {
            return new NotFound();
        }

        $commandBus->dispatch(new StartCampaign($mail->slug));
    }
}

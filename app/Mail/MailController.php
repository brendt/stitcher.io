<?php

namespace App\Mail;

use App\Mail\Models\Subscriber;
use App\Support\Authentication\Admin;
use Tempest\CommandBus\CommandBus;
use Tempest\DateTime\DateTime;
use Tempest\Http\Response;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\StaticPage;
use Tempest\View\View;

use function Tempest\Database\query;
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

    #[Get('/mail/unsubscribe/{?uuid}')]
    public function unsubscribe(?string $uuid = null): View
    {
        return view('mail-unsubscribe.view.php', uuid: $uuid);
    }

    #[Post('/mail/unsubscribe/{uuid}')]
    public function submitUnsubscribe(string $uuid): Redirect
    {
        query(Subscriber::class)
            ->update(
                unsubscribedAt: DateTime::now()
            )
            ->where('uuid', $uuid)
            ->execute();

        return new Redirect('/mail/unsubscribe');
    }

    #[Admin, Post('/mail/send/{slug}')]
    public function send(string $slug, MailRepository $repository, CommandBus $commandBus): Response|View
    {
        $mail = $repository->find($slug);

        if (! $mail) {
            return new NotFound();
        }

        $commandBus->dispatch(new StartMailCampaign($mail->path));

        return new Ok();
    }
}

<?php

namespace App\Mail;

use App\Mail\Models\OutboxCampaign;
use App\Mail\Models\OutboxMail;
use App\Mail\Models\Subscriber;
use Tempest\CommandBus\Async;
use Tempest\CommandBus\CommandHandler;
use Tempest\DateTime\DateTime;
use Tempest\Markdown\Markdown;
use Tempest\View\ViewRenderer;

use function Tempest\Router\uri;
use function Tempest\View\view;

final readonly class MailHandlers
{
    public function __construct(
        private ViewRenderer $viewRenderer,
        private Markdown $markdown,
    ) {}

    #[CommandHandler]
    public function onStartMailCampaign(StartMailCampaign $command): void
    {
        $campaign = OutboxCampaign::create(
            path: $command->path,
            startedAt: DateTime::now(),
        );

        $content = @file_get_contents($command->path);

        if ($content === false) {
            $campaign->update(
                failedAt: DateTime::now(),
                log: "File not found: {$command->path}",
            );

            return;
        }

        $parsed = $this->markdown->parse($content);
        $subject = $parsed->frontmatter['subject'] ?? $parsed->frontmatter['title'] ?? null;

        if (! $subject) {
            $campaign->update(
                failedAt: DateTime::now(),
                log: "Missing subject in frontmatter: {$command->path}",
            );

            return;
        }

        $html = $this->viewRenderer->render(view(
            __DIR__ . '/mail-export.view.php',
            mail: new Mail(
                path: $command->path,
                slug: $command->path,
                title: $parsed->frontmatter['title'] ?? pathinfo($command->path, PATHINFO_FILENAME),
                content: $parsed->html,
                date: DateTime::now(),
                pretext: $parsed->frontmatter['pretext'] ?? null,
            ),
        ));

        Subscriber::select()
            ->whereNull('unsubscribedAt')
            ->chunk(
                function (array $subscribers) use ($campaign, $subject, $html) {
                    /** @var Subscriber $subscriber */
                    foreach ($subscribers as $subscriber) {
                        $unsubUri = uri([MailController::class, 'unsubscribe'], uuid: $subscriber->uuid);

                        $personalizedHtml = str_replace(
                            ['{{ $subscriber }}', '::subscriber.first_name::', '{{ $unsubUri }}', '::unsubscribeUrl::'],
                            [$subscriber->name, $subscriber->name, $unsubUri, $unsubUri],
                            $html,
                        );

                        OutboxMail::create(
                            campaign: $campaign,
                            receiver: $subscriber->email,
                            subject: $subject,
                            content: $personalizedHtml,
                        );
                    }
                },
                1000,
            );

        $campaign->update(
            endedAt: DateTime::now(),
        );
    }
}

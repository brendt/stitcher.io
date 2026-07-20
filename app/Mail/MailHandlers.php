<?php

namespace App\Mail;

use App\Mail\Models\Campaign;
use App\Mail\Models\OutboxMail;
use App\Mail\Models\Subscriber;
use Tempest\Clock\Clock;
use Tempest\CommandBus\CommandHandler;
use Tempest\Markdown\Markdown;
use Tempest\View\ViewRenderer;

use function Tempest\Router\uri;
use function Tempest\View\view;

final readonly class MailHandlers
{
    public function __construct(
        private ViewRenderer $viewRenderer,
        private Markdown $markdown,
        private Clock $clock,
    ) {}

    #[CommandHandler]
    public function onStartMailCampaign(StartMailCampaign $command): void
    {
        $campaign = Campaign::get($command->campaignId);

        if ($campaign->processedAt) {
            return;
        }

        $content = @file_get_contents($campaign->path);

        if ($content === false) {
            $campaign->update(
                failedAt: $this->clock->now(),
                log: "File not found: {$campaign->path}",
            );

            return;
        }

        $parsed = $this->markdown->parse($content);
        $subject = $parsed->frontmatter['subject'] ?? $parsed->frontmatter['title'] ?? null;

        if (! $subject) {
            $campaign->update(
                failedAt: $this->clock->now(),
                log: "Missing subject in frontmatter: {$campaign->path}",
            );

            return;
        }

        $html = $this->viewRenderer->render(view(
            __DIR__ . '/mail-export.view.php',
            mail: new Mail(
                path: $campaign->path,
                slug: $campaign->path,
                title: $parsed->frontmatter['title'] ?? pathinfo($campaign->path, PATHINFO_FILENAME),
                content: $parsed->html,
                date: $this->clock->now(),
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
            processedAt: $this->clock->now(),
        );
    }
}

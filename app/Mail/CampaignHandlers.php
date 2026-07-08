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

use function Tempest\View\view;

final readonly class CampaignHandlers
{
    public function __construct(
        private ViewRenderer $viewRenderer,
        private Markdown $markdown,
    ) {}

    #[Async, CommandHandler]
    public function onStartCampaign(StartCampaign $startCampaign): void
    {
        $campaign = OutboxCampaign::create(
            path: $startCampaign->path,
            startedAt: DateTime::now(),
        );

        $content = @file_get_contents($startCampaign->path);

        if ($content === false) {
            $campaign->update(
                failedAt: DateTime::now(),
                log: "File not found: {$startCampaign->path}",
            );

            return;
        }

        $parsed = $this->markdown->parse($content);
        $subject = $parsed->frontmatter['subject'] ?? $parsed->frontmatter['title'] ?? null;

        if (! $subject) {
            $campaign->update(
                failedAt: DateTime::now(),
                log: "Missing subject in frontmatter: {$startCampaign->path}",
            );

            return;
        }

        $html = $this->viewRenderer->render(view(
            __DIR__ . '/mail-export.view.php',
            mail: new Mail(
                slug: $startCampaign->path,
                title: $parsed->frontmatter['title'] ?? pathinfo($startCampaign->path, PATHINFO_FILENAME),
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
                        $html = str_replace(
                            ['{{ $subscriber }}', '{{ $unsubUri }}'],
                            [$subscriber->name, ''],
                            $html,
                        );

                        OutboxMail::create(
                            campaign: $campaign,
                            receiver: $subscriber->email,
                            subject: $subject,
                            content: $html,
                        );
                    }
                },
                1000,
            );
    }
}

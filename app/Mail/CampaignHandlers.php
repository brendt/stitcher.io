<?php

namespace App\Mail;

use App\Mail\Models\OutboxCampaign;
use App\Mail\Models\OutboxMail;
use App\Mail\Models\Subscriber;
use Symfony\Component\Uid\Uuid;
use Tempest\Clock\Clock;
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
        private Clock $clock,
    ) {}

    #[Async, CommandHandler]
    public function onStartCampaign(StartCampaign $startCampaign): void
    {
        $parsed = $this->markdown->parse(file_get_contents($startCampaign->path));

        $html = $this->viewRenderer->render(view(
            __DIR__ . '/mail-export.view.php',
            mail: new Mail(
                slug: $startCampaign->path,
                title: $parsed->frontmatter['title'] ?? pathinfo($startCampaign->path, PATHINFO_FILENAME),
                content: $parsed->html,
                date: $this->clock->now(),
                pretext: $parsed->frontmatter['pretext'] ?? null,
            ),
        ));

        $campaign = OutboxCampaign::create(
            path: $startCampaign->path,
            startedAt: $this->clock->now(),
        );

        Subscriber::select()
            ->whereNull('unsubscribedAt')
            ->chunk(function (array $subscribers) use ($campaign, $parsed, $html) {
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
                        subject: $parsed->frontmatter['title'],
                        content: $html,
                    );
                }
            }, 1000);
    }
}
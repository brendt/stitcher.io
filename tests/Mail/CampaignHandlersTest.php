<?php

namespace Tests\Mail;

use App\Mail\CampaignHandlers;
use App\Mail\Models\OutboxCampaign;
use App\Mail\Models\OutboxMail;
use App\Mail\Models\Subscriber;
use App\Mail\StartCampaign;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Mail\Mailer;
use Tempest\Mail\Testing\TestingMailer;
use Tests\IntegrationTestCase;
use function Tempest\Database\query;
use function Tempest\Framework\Testing\factory;

final class CampaignHandlersTest extends IntegrationTestCase
{
    #[PreCondition]
    public function setupTestingMailer(): void
    {
        $this->container->singleton(Mailer::class, new TestingMailer());
    }

    #[Test]
    public function test_start_campaign(): void
    {
        $now = $this->clock()->now();
        $this->database->reset();

        factory(Subscriber::class)->with(email: 'sub@stitcher.io', name: 'Brent')->times(10)->save();
        factory(Subscriber::class)->with(email: 'unsub@stitcher.io', unsubscribedAt: $now)->times(10)->save();

        /** @var CampaignHandlers $handlers */
        $handlers = $this->container->get(CampaignHandlers::class);

        $path = __DIR__ . '/2026-07-07-test.md';

        $handlers->onStartCampaign(new StartCampaign($path));

        $this->assertSame(
            1,
            query(OutboxCampaign::class)
                ->count()
                ->where('path', $path)
                ->where('startedAt', $now)
                ->whereNull('endedAt')
                ->whereNull('failedAt')
                ->execute(),
        );

        $this->assertSame(
            10,
            query(OutboxMail::class)
                ->count()
                ->where('receiver', 'sub@stitcher.io')
                ->where('subject', 'Test Mail')
                ->whereNotNull('content')
                ->whereLike('content', '%<p>Hello world</p>%')
                ->whereLike('content', '%Hi Brent%')
                ->execute(),
        );

        $this->assertSame(
            0,
            query(OutboxMail::class)
                ->count()
                ->where('receiver', 'unsub@stitcher.io')
                ->execute(),
        );
    }
}

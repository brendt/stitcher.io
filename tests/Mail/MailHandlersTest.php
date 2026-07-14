<?php

namespace Tests\Mail;

use App\Mail\MailHandlers;
use App\Mail\Models\OutboxCampaign;
use App\Mail\Models\OutboxMail;
use App\Mail\Models\Subscriber;
use App\Mail\StartMailCampaign;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\DateTime\DateTime;
use Tempest\Mail\Mailer;
use Tempest\Mail\Testing\TestingMailer;
use Tests\IntegrationTestCase;

use function Tempest\Database\query;
use function Tempest\Framework\Testing\factory;

final class MailHandlersTest extends IntegrationTestCase
{
    #[PreCondition]
    public function setupTestingMailer(): void
    {
        $this->container->singleton(Mailer::class, new TestingMailer());
    }

    #[Test]
    public function test_start_mail_campaign(): void
    {
        $this->database->reset();

        factory(Subscriber::class)->with(email: 'sub@stitcher.io', name: 'Brent')->times(10)->save();
        factory(Subscriber::class)->with(email: 'unsub@stitcher.io', unsubscribedAt: DateTime::now())->times(10)->save();

        /** @var MailHandlers $handlers */
        $handlers = $this->container->get(MailHandlers::class);

        $path = __DIR__ . '/2026-07-07-test.md';

        $handlers->onStartMailCampaign(new StartMailCampaign($path));

        $this->assertSame(
            1,
            query(OutboxCampaign::class)
                ->count()
                ->where('path', $path)
                ->where('startedAt', DateTime::now())
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

    #[Test]
    public function test_start_mail_campaign_with_invalid_path(): void
    {
        $this->database->reset();

        /** @var MailHandlers $handlers */
        $handlers = $this->container->get(MailHandlers::class);

        $path = 'invalid';

        $handlers->onStartMailCampaign(new StartMailCampaign($path));

        $this->assertSame(
            1,
            query(OutboxCampaign::class)
                ->count()
                ->where('path', $path)
                ->where('startedAt', DateTime::now())
                ->where('failedAt', DateTime::now())
                ->where('log', "File not found: {$path}")
                ->execute(),
        );
    }

    #[Test]
    public function test_start_mail_campaign_with_missing_subject(): void
    {
        $this->database->reset();

        /** @var MailHandlers $handlers */
        $handlers = $this->container->get(MailHandlers::class);

        $path = __DIR__ . '/2026-07-07-invalid.md';

        $handlers->onStartMailCampaign(new StartMailCampaign($path));

        $this->assertSame(
            1,
            query(OutboxCampaign::class)
                ->count()
                ->where('path', $path)
                ->where('startedAt', DateTime::now())
                ->where('failedAt', DateTime::now())
                ->where('log', "Missing subject in frontmatter: {$path}")
                ->execute(),
        );
    }
}

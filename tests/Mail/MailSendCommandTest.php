<?php

namespace Tests\Mail;

use App\Mail\Models\OutboxCampaign;
use App\Mail\Models\OutboxMail;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Mail\Mailer;
use Tempest\Mail\Testing\TestingMailer;
use Tests\IntegrationTestCase;

use function Tempest\Framework\Testing\factory;

class MailSendCommandTest extends IntegrationTestCase
{
    private TestingMailer $testingMailer;

    #[PreCondition]
    public function setupTestingMailer(): void
    {
        $this->testingMailer = new TestingMailer();

        $this->container->singleton(Mailer::class, $this->testingMailer);
    }

    #[Test]
    public function test_failed_mails_log_error(): void
    {
        $this->container->singleton(Mailer::class, new ExceptionMailer());
        $now = $this->clock()->now();
        $this->database->reset();

        $campaign = factory(OutboxCampaign::class)->save();

        factory(OutboxMail::class)
            ->with(campaign: $campaign)
            ->save();

        $this->console->call('mail:send');

        $this->assertSame(
            1,
            OutboxMail::count()
                ->whereNull('sentAt')
                ->where('failedAt', $now)
                ->whereNotNull('log')
                ->whereLike('log', '%Nope%')
                ->whereLike('log', '%ExceptionMailer%')
                ->execute(),
        );
    }

    #[Test]
    public function test_mail_data_is_used_when_sending(): void
    {
        $this->database->reset();

        $campaign = factory(OutboxCampaign::class)->save();

        factory(OutboxMail::class)
            ->with(
                campaign: $campaign,
                receiver: 'brendt@stitcher.io',
                content: '<p>hi</p>',
                subject: 'Test',
            )
            ->save();

        $this->console->call('mail:send');

        $sent = $this->testingMailer->sent[0] ?? null;

        $this->assertNotNull($sent);
        $this->assertSame('brendt@stitcher.io', $sent->to);
        $this->assertSame('<p>hi</p>', $sent->html);
        $this->assertSame('Test', $sent->subject);
    }

    #[Test]
    public function test_chunked_per_1000(): void
    {
        $this->container->singleton(Mailer::class, new NullMailer());
        $now = $this->clock()->now();
        $this->database->reset();

        $campaign = factory(OutboxCampaign::class)->save();

        factory(OutboxMail::class)
            ->with(campaign: $campaign)
            ->times(2)
            ->save();

        $this->console->call('mail:send --chunk=1');

        $this->assertSame(
            1,
            OutboxMail::count()
                ->where('sendingAt', $now)
                ->where('sentAt', $now)
                ->execute(),
        );

        $this->assertSame(
            1,
            OutboxMail::count()
                ->whereNull('sendingAt')
                ->execute(),
        );
    }

    #[Test]
    public function test_mails_are_marked_with_sending_at_and_sent_at(): void
    {
        $now = $this->clock()->now();
        $this->database->reset();

        $campaign = factory(OutboxCampaign::class)->save();

        factory(OutboxMail::class)
            ->with(campaign: $campaign)
            ->times(10)
            ->save();

        $this->console->call('mail:send');

        $this->assertCount(10, $this->testingMailer->sent);

        $this->assertSame(
            10,
            OutboxMail::count()
                ->where('sendingAt', $now)
                ->where('sentAt', $now)
                ->execute(),
        );
    }
}

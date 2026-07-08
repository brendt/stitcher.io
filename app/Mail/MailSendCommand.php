<?php

namespace App\Mail;

use App\Mail\Models\OutboxMail;
use Symfony\Component\Uid\Uuid;
use Tempest\Clock\Clock;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;
use Tempest\Mail\GenericEmail;
use Tempest\Mail\Mailer;
use Throwable;

use function Tempest\Database\query;
use function Tempest\env;
use function Tempest\Support\arr;

final readonly class MailSendCommand
{
    use HasConsole;

    public function __construct(
        private Clock $clock,
        private Mailer $mailer,
    ) {}

    #[ConsoleCommand, Schedule(Every::MINUTE)]
    public function __invoke(int $chunk = 1000): void
    {
        $mails = query(OutboxMail::class)
            ->select()
            ->whereNull('sendingAt')
            ->whereNull('failedAt')
            ->whereNull('sentAt')
            ->limit($chunk)
            ->all();

        $ids = arr($mails)->map(fn (OutboxMail $outboxMail) => $outboxMail->id->value)->toArray();

        query(OutboxMail::class)
            ->update(
                sendingAt: $this->clock->now(),
            )
            ->whereIn('id', $ids)
            ->execute();

        /** @var OutboxMail $mail */
        foreach ($mails as $mail) {
            try {
                $this->mailer->send(new GenericEmail(
                    subject: $mail->subject,
                    to: $mail->receiver,
                    html: $mail->content,
                    from: env('MAIL_FROM_ADDRESS', 'brendt@stitcher.io'),
                    replyTo: env('MAIL_FROM_ADDRESS', 'brendt@stitcher.io'),
                ));

                $mail->update(
                    sentAt: $this->clock->now(),
                );
            } catch (Throwable $e) {
                $mail->update(
                    failedAt: $this->clock->now(),
                    log: $e->getMessage() . PHP_EOL . $e->getTraceAsString(),
                );
            }
        }
    }
}

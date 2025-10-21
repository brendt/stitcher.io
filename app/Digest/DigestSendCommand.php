<?php

namespace App\Digest;

use App\Authentication\Role;
use App\Authentication\User;
use App\Blog\Comment;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;
use Tempest\DateTime\DateTime;
use Tempest\Mail\GenericEmail;
use Tempest\Mail\Mailer;
use function Tempest\Database\query;
use function Tempest\view;

final class DigestSendCommand
{
    use HasConsole;

    public function __construct(
        private readonly Mailer $mailer,
    ) {}

    #[ConsoleCommand]
    #[Schedule(Every::DAY)]
    public function __invoke(): void
    {
        $admin = User::select()->where('role', Role::ADMIN)->first();

        if (! $admin) {
            $this->error('No admin user found');
            return;
        }

        $latestDigest = query(CommentDigest::class)
            ->select()
            ->orderBy('createdAt DESC')
            ->first();

        $date = $latestDigest->createdAt ?? DateTime::now()->startOfDay();

        $comments = Comment::select()
            ->where('createdAt >= ?', $date)
            ->with('user')
            ->all();

        if ($comments === []) {
            $this->info('No new comments');
            return;
        }

        $this->mailer->send(new GenericEmail(
            subject: 'New blog comments',
            to: $admin->email,
            html: view(
                __DIR__ . '/mail-digest.view.php',
                comments: $comments,
            ),
        ));

        CommentDigest::create(
            createdAt: DateTime::now()
        );

        $this->info("Sent digest to {$admin->email}");
    }
}
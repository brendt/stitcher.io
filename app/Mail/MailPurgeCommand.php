<?php

namespace App\Mail;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Core\Environment;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\FormatPattern;
use function Tempest\Database\query;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final class MailPurgeCommand
{
    use HasConsole;

    public function __construct(
        private readonly Environment $environment,
    ) {}

    #[ConsoleCommand]
    public function __invoke(): void
    {
        if (! $this->environment->isLocal()) {
            $this->error('This command can only be run locally.');

            return;
        }

        $emails = [];

        $input = fopen(__DIR__ . '/input', 'r');

        while ($line = fgets($input)) {
            $line = str($line);

            if (! $line->startsWith([
                'Final-Recipient',
                'X-Failed-Recipients',
            ])) {
                continue;
            }

            $email = $line
                ->afterFirst(':')
                ->afterFirst('rfc822;')
                ->trim()
                ->toString();

            $emails[$email] = $email;
        }

        fclose($input);

        $output = fopen(__DIR__ . '/output.sql', 'w');
        fwrite($output, query('mailcoach_subscribers')->select()->whereIn('email', $emails)->toRawSql() . ';');
        fwrite($output, PHP_EOL);
        fwrite($output, query('mailcoach_subscribers')->update(unsubscribed_at: DateTime::now()->format(FormatPattern::SQL_DATE_TIME))->whereIn('email', $emails)->toRawSql() . ';');
        fclose($output);

        $this->info('Done, found ' . count($emails) . ' emails.');
    }
}
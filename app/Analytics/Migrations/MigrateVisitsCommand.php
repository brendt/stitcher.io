<?php

namespace App\Analytics\Migrations;

use App\Analytics\PageVisited;
use DateTimeImmutable;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use function Tempest\Database\query;
use function Tempest\EventBus\event;

final class MigrateVisitsCommand
{
    use HasConsole;

    #[ConsoleCommand]
    public function __invoke(): void
    {
        $this->writeln('Preparing…');

        $lastId = 0;

        if (is_file(__DIR__ . '/last-id')) {
            $lastId = file_get_contents(__DIR__ . '/last-id');
        }

        $totalCount = query('visits')
            ->count()
            ->where('id > ?', $lastId)
            ->execute();

        $currentCount = 0;

        query('visits')
            ->select()
            ->where('id > ?', $lastId)
            ->chunk(function (array $visits) use ($totalCount, &$currentCount) {
                $lastId = 0;

                foreach ($visits as $visit) {
                    event(new PageVisited(
                        url: $visit['url'],
                        visitedAt: new DateTimeImmutable($visit['date']),
                        ip: $visit['ip'],
                        userAgent: $visit['user_agent'] ?? '',
                        raw: $visit['payload'],
                    ));

                    $lastId = $visit['id'];
                }

                $currentCount += count($visits);

                $this->writeln(sprintf(
                    '%s (%s/%s)',
                    floor($currentCount / $totalCount * 100) . '%',
                    number_format($currentCount),
                    number_format($totalCount),
                ));

                file_put_contents(__DIR__ . '/last-id', $lastId);
            }, 1000);
    }
}
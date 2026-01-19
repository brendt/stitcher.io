<?php

declare(strict_types=1);

namespace App\Support\StoredEvents;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Middleware\ForceMiddleware;
use Tempest\Container\Container;
use function Tempest\Database\query;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final readonly class EventsReplayCommand
{
    use HasConsole;

    public function __construct(
        private StoredEventConfig $storedEventConfig,
        private Console $console,
        private Container $container,
    ) {}

    #[ConsoleCommand(aliases: ['replay'], middleware: [ForceMiddleware::class])]
    public function __invoke(?string $replay = null): void
    {
        $projectors = arr($this->storedEventConfig->projectors)->sort();

        if ($projectors->isEmpty()) {
            $this->error('No projectors found');
            return;
        }

        if ($replay) {
            $replay = [$replay];
        } else { // @mago-expect lint:no-else-clause
            $replay = $this->ask(
                question: 'Which projectors should be replayed?',
                options: $projectors->toArray(),
                multiple: true,
            );
        }

        $replayCount = count($replay);

        if (! $replayCount) {
            $this->error('No projectors selected');

            return;
        }

//        $eventCount = query('stored_events')->count()->execute();
        $eventCount = 1;

        $confirm = $this->confirm(sprintf(
            'We\'re going to replay %d events on %d %s, this will take a while. Continue?',
            $eventCount,
            $replayCount,
            str('projector')->pluralize($replayCount),
        ), default: true);

        if (! $confirm) {
            $this->error('Cancelled');

            return;
        }

        $projectors = $projectors
            ->filter(fn (string $projectorClass) => in_array($projectorClass, $replay, strict: true))
            ->map(fn (string $projectorClass) => $this->container->get($projectorClass));

        $this->info("Clearing projectors…");

        foreach ($projectors as $projector) {
            $projector->clear();
        }

        $this->success('Done');

        $this->info("Replaying events…");

        $currentCount = 0;
        $startTime = microtime(true);

        $offset = 0;
        $limit = 1500;
        $currentEps = 0;

        while ($data = query('stored_events')->select()->offset($offset)->limit($limit)->all()) {
            // Setup
            $events = arr($data)
                ->map(function (array $item) {
                    return $item['eventClass']::unserialize($item['payload']);
                })
                ->toArray();

            // Loop
            foreach ($projectors as $projector) {
                foreach ($events as $event) {

                    $projector->replay($event);
                }
            }

            // Metrics
            $currentCount += count($events);
            $currentTime = microtime(true);
            $timeElapsed = $currentTime - $startTime;
            $previousEps = $currentEps;
            $currentEps = round($currentCount / $timeElapsed);

            $this->writeln(sprintf(
                '%s/%s — %s — <style="%s">%s/eps</style>',
                number_format($currentCount),
                number_format($eventCount),
                $this->memory(),
                $currentEps > $previousEps ? 'fg-green' : 'fg-red',
                $currentEps,
            ));
        }

        $this->success('Done');
    }

    private function memory(): string
    {
        $memory = memory_get_usage(true);

        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

        return @round($memory / pow(1024, ($i = floor(log($memory, 1024)))), 2) . $unit[$i];
    }
}

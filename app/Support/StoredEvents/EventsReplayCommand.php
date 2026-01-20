<?php

declare(strict_types=1);

namespace App\Support\StoredEvents;

use Tempest\Cache\Cache;
use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Middleware\ForceMiddleware;
use Tempest\Container\Container;
use Tempest\DateTime\Duration;
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
        private Cache $cache,
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

        $this->info('Gathering events…');

        $eventCount = $this->cache->resolve(
            'event-count',
            fn () => query('stored_events')->count()->execute(),
            Duration::hour(),
        );

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

        $startTime = microtime(true);
        $eventsProcessed = 0;
        $currentEps = 0;

        $lastId = 0;
        $limit = 1500;

        while ($data = query('stored_events')->select()->where('id > ?', $lastId)->limit($limit)->all()) {
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

                if ($projector instanceof BufferedProjector) {
                    $projector->persist();
                }
            }

            // Metrics
            $eventsProcessed += count($events);
            $currentTime = microtime(true);
            $timeElapsed = $currentTime - $startTime;
            $previousEps = $currentEps;
            $currentEps = round($eventsProcessed / $timeElapsed);
            $timeRemaining = round(($eventCount - $eventsProcessed) / $currentEps);

            $this->writeln(sprintf(
                '%s/%s — %s — <style="%s">%s/eps</style> — %ss left — %s offset',
                number_format($eventsProcessed),
                number_format($eventCount),
                $this->memory(),
                $currentEps > $previousEps ? 'fg-green' : 'fg-red',
                $currentEps,
                $timeRemaining > 0 ? $timeRemaining : '0',
                $lastId,
            ));

            $lastId = array_last($data)['id'];
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

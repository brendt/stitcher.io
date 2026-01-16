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

    #[ConsoleCommand(middleware: [ForceMiddleware::class])]
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

        $eventCount = query(StoredEvent::class)->count()->execute();

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

        foreach ($projectors as $projectorClass) {
            if (! in_array($projectorClass, $replay, strict: true)) {
                continue;
            }

            $this->info(sprintf('Replaying <style="underline">%s</style>', $projectorClass));

            /** @var Projector $projector */
            $projector = $this->container->get($projectorClass);

            $projector->clear();

            StoredEvent::select()
                ->orderBy('createdAt ASC')
                ->chunk(
                    function (array $storedEvents) use ($projector): void {
                        $this->write('.');

                        foreach ($storedEvents as $storedEvent) {
                            $projector->replay($storedEvent->getEvent());
                        }
                    },
                    500,
                );

            $this->writeln();
        }

        $this->success('Done');
    }
}

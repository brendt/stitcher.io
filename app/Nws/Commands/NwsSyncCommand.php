<?php

namespace App\Nws\Commands;

use App\Nws\Nws;
use Tempest\Cache\Cache;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\Duration;
use Throwable;

final class NwsSyncCommand
{
    use HasConsole;

    public function __construct(
        private readonly Cache $cache,
    ) {}

    #[ConsoleCommand('nws:sync'), Schedule(Every::HOUR)]
    public function __invoke(): void
    {
        $xml = $this->cache->resolve(
            'nws',
            fn () => file_get_contents('https://www.vrt.be/vrtnws/nl.rss.articles.xml'),
            Duration::minutes(30),
        );

        $items = $this->parseXml($xml);

        foreach ($items as $item) {
            try {
                $summary = is_string($item['summary'] ?? null) ? $item['summary'] : '';
                $title = ($item['title'] ?? null) ? $item['title'] : '';

                if (! $title) {
                    continue;
                }

                $nws = Nws::updateOrCreate(
                    ['uri' => $item['id']],
                    [
                        'title' => $item['title'],
                        'summary' => $summary,
                        'publishedAt' => DateTime::parse($item['published']),
                        'tag' => $item['tag'] ?? '',
                    ],
                );

                $this->success($nws->title);
            } catch (Throwable $e) {
                $this->error($e->getMessage());
            }
        }

        $this->success('Done');
    }

    private function parseXml(string $input): array
    {
        $input = str_replace('vrtns:nstag', 'tag', $input);

        $xml = simplexml_load_string($input, "SimpleXMLElement", LIBXML_NOWARNING | LIBXML_NOERROR);

        if (! $xml) {
            return [];
        }
        $json = json_encode($xml);

        return json_decode($json, true, flags: JSON_THROW_ON_ERROR)['entry'];
    }
}
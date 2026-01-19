<?php

declare(strict_types=1);

namespace App\Analytics;

use DateTimeImmutable;
use Tempest\Clock\Clock;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use function Tempest\Database\query;
use function Tempest\EventBus\event;
use function Tempest\Support\str;

final class ParseLogCommand
{
    use HasConsole;

    public function __construct(
        private readonly Clock $clock,
        private readonly AnalyticsConfig $config,
    ) {}

    private const array URL_BLACKLIST = [
        '.png',
        'spotify',
        '/site.webmanifest',
        '/analytics.js',
        '/stats',
        '/rss',
        '/analytics',
        '/img',
        '/rss/',
        '/build/assets',
        '/feed',
        '/feed',
        '/feed/',
        '/favicon.ico',
        '.env',
        '.jpg',
        '.jpeg',
        '.gif',
        '.git/HEAD',
        '.tar',
        '.gz',
        '.zip',
        '.bz2',
        '/robots.txt',
        '/feed.xml',
        '/rss.xml',
    ];

    private const array USER_AGENT_BLACKLIST = [
        'zapier',
        'curl',
        'guzzle',
        'bot',
        'spider',
        'ohdear',
        'crawl',
        'feed',
        'rss',
        'python',
        'uptime-kuma',
    ];

    private const array IP_BLACKLIST = [
        '45.155.205.141',
        '35.246.133.173',
        '34.89.200.43',
        '130.255.160.128',
        '185.13.96.91',
    ];

    /** @var DateTimeImmutable[] */
    private static array $ips = [];

    #[ConsoleCommand]
    public function __invoke(?string $path = null): void
    {
        $path = $path ?? $this->config->accessLogPath;

        $handle = fopen($path, 'r');

        // Resolve the last stored date
        $lastDate = query('stored_events')
            ->select()
            ->where('eventClass = ?', PageVisited::class)
            ->limit(1)
            ->orderBy('id DESC')
            ->first();

        if ($lastDate) {
            $lastDate = new DateTimeImmutable($lastDate['createdAt']);
        }

        $this->success(sprintf("Parsing <style=\"underline\">%s</style>", $path));

        while (true) {
            $line = str(fgets($handle) ?: '')->trim();

            if ($line->isEmpty()) {
                usleep(100000);
                fseek($handle, ftell($handle));
                continue;
            }

            // Resolve and check date
            $date = $line->match("/\[([\w\d\/\:]+)/");

            if (! $date) {
                continue;
            }

            $date = new DateTimeImmutable($date . ' +0000');

            if ($lastDate && $lastDate >= $date) {
                continue;
            }

            // Resolve and check URL
            $url = str($line->match("/(GET|POST)\s([\w\/\.\-]+)/", match: 2));

            // Empty URL
            if ($url->equals('')) {
                continue;
            }

            // URL Blacklist
            if ($url->endsWith(self::URL_BLACKLIST) || $url->startsWith(self::URL_BLACKLIST)) {
                continue;
            }

            // Resolve and check IP
            $ip = $line->match("/^([\d\.\w\:]+)/");

            if (in_array($ip, self::IP_BLACKLIST, strict: true)) {
                continue;
            }

            // Resolve and check UserAgent
            $userAgent = str($line->match('/"([\,\-\w\d\.\/\s\(\)\:\;\+\=\&\~\\\\\@\{\}\%\'\!\?\[\]\<\>\|]+)"$/'))->lower();

            foreach (self::USER_AGENT_BLACKLIST as $blockedUserAgent) {
                if (! $userAgent->contains($blockedUserAgent)) {
                    continue;
                }

                continue 2;
            }

            // Create event
            $event = new PageVisited(
                url: $url->toString(),
                visitedAt: $date,
                ip: $ip,
                userAgent: $userAgent->toString(),
                raw: $line->toString(),
            );

            $previousDateForIp = self::$ips[$event->ip] ?? null;

            if ($previousDateForIp && $previousDateForIp->diff($event->visitedAt)->s < 1) {
                self::$ips[$event->ip] = $event->visitedAt;

                $this->writeln(sprintf("<style=\"bg-red fg-white\">%s </style> %s (throttled)", $date->format('Y-m-d H:i:s'), $event->url));

                continue;
            }

            event($event);

            self::$ips[$event->ip] = $event->visitedAt;

            $this->writeln(sprintf("<style=\"bg-blue fg-white\">%s </style> %s", $date->format('Y-m-d H:i:s'), $event->url));
        }
    }
}

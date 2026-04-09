<?php

namespace App\Analytics;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Core\Environment;
use Tempest\DateTime\DateTime;

final class TestLogCommand
{
    use HasConsole;

    public function __construct(
        private Environment $environment,
        private readonly AnalyticsConfig $config,
    ) {}

    #[ConsoleCommand]
    public function __invoke(): void
    {
        if (! $this->environment->isLocal()) {
            $this->error('This command can only be run locally.');
            return;
        }

        $path = $this->config->accessLogPath;

        $handle = fopen($path, 'a');

        foreach (range(1, 10) as $i) {
//            if (rand(0, 1) === 1) {
//                fputs($handle, '157.15.40.6 - - [' . DateTime::now()->plusSeconds($i * 3)->format('dd/LLL/yyyy:HH:mm:ss') . ' 0] "GET /test/' . $i . ' HTTP/2.0" 200 319261 "-" "GuzzleHttp/6.3.3 curl/7.58.0 PHP/7.2.34"' . PHP_EOL);
//            } else {
//                fputs($handle, '157.15.40.6 - - [' . DateTime::now()->plusSeconds($i * 3)->format('dd/LLL/yyyy:HH:mm:ss') . ' 0] "GET /test/' . $i . ' HTTP/2.0" 200 319261 "-" "unknown"' . PHP_EOL);
//            }

            fputs(
                $handle,
                '157.15.40.6 - - [' . DateTime::now()->plusSeconds($i * 3)->format('dd/LLL/yyyy:HH:mm:ss') . ' 0] "GET /test/' . $i . ' HTTP/1.1" 301 162 "-" "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.102 Safari/537.36"' . PHP_EOL
            );

            usleep(1000);
        }
    }
}
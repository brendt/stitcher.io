<?php

namespace App\Blog\VersionStats\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final readonly class PackageStatsConvertCommand
{
    use HasConsole;

    #[ConsoleCommand(name: 'convert:package-stats')]
    public function __invoke(): void
    {
        $handle = fopen(__DIR__ . '/../Data/package-stats.csv', 'r');

        /** @var array<int, string> $headers */
        $headers = [];
        /** @var array<string, array<string, string>> $data */
        $data = [];

        while ($line = fgetcsv($handle)) {
            if ($line[0] === 'version') {
                $headers = array_map(static fn (?string $header): string => $header ?? '', $line);
                continue;
            }

            $version = null;

            foreach ($line as $index => $value) {
                if ($index === 0) {
                    $version = is_string($value) ? $value : null;
                    continue;
                }

                if ($version === null || ! isset($headers[$index]) || $headers[$index] === '' || ! is_string($value)) {
                    continue;
                }

                $data[$headers[$index]] ??= [];
                $data[$headers[$index]][$version] = $value;
            }

            foreach ($data as $date => $month) {
                ksort($month);
                $data[$date] = $month;
            }
        }

        fclose($handle);

        file_put_contents(__DIR__ . '/../Data/package-stats.json', json_encode($data, JSON_PRETTY_PRINT));

        $this->info('Done');
    }
}

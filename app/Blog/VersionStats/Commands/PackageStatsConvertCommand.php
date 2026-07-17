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

        $headers = [];
        $data = [];

        while ($line = fgetcsv($handle)) {
            if ($line[0] === 'version') {
                $headers = $line;
                continue;
            }

            $version = null;

            foreach ($line as $index => $value) {
                if ($index === 0) {
                    $version = $value;
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

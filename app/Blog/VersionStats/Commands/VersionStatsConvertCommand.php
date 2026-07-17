<?php

namespace App\Blog\VersionStats\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final readonly class VersionStatsConvertCommand
{
    use HasConsole;

    #[ConsoleCommand(name: 'convert:version-stats')]
    public function __invoke(): void
    {
        $handle = fopen(__DIR__ . '/../Data/version-stats.csv', 'r');

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
        }

        fclose($handle);

        file_put_contents(__DIR__ . '/../Data/version-stats.json', json_encode($data, JSON_PRETTY_PRINT));

        $this->info('Done');
    }
}

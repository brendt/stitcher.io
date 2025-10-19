<?php

namespace App\Migrations;

use Symfony\Component\Yaml\Yaml;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\DateTime\DateTime;

final class MigrateMailCommand
{
    use HasConsole;

    #[ConsoleCommand]
    public function __invoke(): void
    {
        $mails = Yaml::parseFile(__DIR__ . '/mail.yaml');

        if (! is_dir(__DIR__ . '/../Mail/Content')) {
            mkdir(__DIR__ . '/../Mail/Content');
        }

        foreach ($mails as $slug => $mail) {
            $fileName = pathinfo($mail['content'], PATHINFO_BASENAME);

            $path = __DIR__ . '/Mail/' . $fileName;

            $content = @file_get_contents($path);

            if (! $content) {
                $this->error($path);
                continue;
            }

            $date = DateTime::parse($mail['date']);

            $newPath = __DIR__ . '/../Mail/Content/' . $date->format('YYYY-MM-dd') . '-' . $slug . '.md';

            unset($mail['date'], $mail['content']);

            $frontMatter = "---\n" . Yaml::dump($mail) . "---\n\n";

            file_put_contents($newPath, $frontMatter . $content);

            $this->success($newPath);
        }
    }
}
<?php

namespace App\Migrations;

use Symfony\Component\Yaml\Yaml;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\DateTime\DateTime;

final class MigrateContentCommand
{
    use HasConsole;

    #[ConsoleCommand]
    public function __invoke(): void
    {
        $posts = Yaml::parseFile(__DIR__ . '/blog.yaml');

        if (! is_dir(__DIR__ . '/../Blog/Content')) {
            mkdir(__DIR__ . '/../Blog/Content');
        }

        foreach ($posts as $slug => $post) {
            $fileName = pathinfo($post['content'], PATHINFO_BASENAME);

            $path = __DIR__ . '/Content/' . $fileName;

            $content = @file_get_contents($path);

            if (! $content) {
                $this->error($path);
                continue;
            }

            $date = DateTime::parse($post['date']);

            $newPath = __DIR__ . '/../Blog/Content/' . $date->format('YYYY-MM-dd') . '-' . $slug . '.md';

            unset($post['date'], $post['content']);

            $frontMatter = "---\n" . Yaml::dump($post) . "---\n\n";

            file_put_contents($newPath, $frontMatter . $content);

            $this->success($newPath);
        }
    }
}
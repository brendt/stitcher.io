<?php

namespace Brendt\Stitcher\Console;

use Stitcher\App;
use Stitcher\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class RedditAnalysisCommand extends Command
{
    public function __construct()
    {
        parent::__construct('reddit:analyse');

        $this->addOption('--clean', null, InputOption::VALUE_OPTIONAL, '', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $submissions = collect($this->getSubmissions($input, $output))
            ->filter(fn(array $item) => $item['domain'] === 'stitcher.io')
            ->map(function (array $item) {
                $isGilded = count($item['gildings']) > 0;

                return [
                    'url' => parse_url($item['url'], PHP_URL_PATH),
                    'subreddit' => $item['subreddit_name_prefixed'] ?? '',
                    'score' => $item['score'] ?? '',
                    'date' => date('Y-m-d', $item['created']),
                    'gilded' => $isGilded ? '⭐️' : '',
                ];
            })
            ->groupBy(fn(array $item) => $item['url']);

        $posts = array_reverse($this->getPosts());

        $table = new Table($output);

        $table->setHeaders(['URL', 'Subreddit', 'Score', 'Date', 'Gilded']);

        $count = 0;

        $rows = [];

        foreach ($posts as $id => $post) {
            $isFirstForUrl = true;

            $url = "/blog/{$id}";

            $submissionsForPost = $submissions[$url] ?? [];

            if (! count($submissionsForPost)) {
                $rows[] = [
                    $url,
                ];

                $rows[] = new TableSeparator();

                continue;
            }

            foreach ($submissionsForPost as $submission) {
                $row = [
                    $isFirstForUrl ? $url : '',
                    $submission['subreddit'],
                    $submission['score'],
                    $submission['date'],
                    $submission['gilded'],
                ];

                $rows[] = $row;

                $isFirstForUrl = false;

                $count++;
            }

            $rows[] = new TableSeparator();
        }

        unset($rows[count($rows) - 1]);

        $table->setRows($rows);

        $table->render();

        $output->writeln("Total {$count}");
    }

    private function getPosts(): array
    {
        /** @var \Symfony\Component\Yaml\Yaml $yaml */
        $yaml = App::get(Yaml::class);

        $posts = $yaml->parse(file_get_contents(File::path('src/content/blog.yaml')));

        return $posts;
    }

    private function getSubmissions(InputInterface $input, OutputInterface $output): array
    {
        $cachePath = __DIR__ . '/submissions.json';

        if ($input->getOption('clean') === false && file_exists($cachePath)) {
            return json_decode(file_get_contents($cachePath), true);
        }

        $submissions = [];

        $after = null;

        do {
            $url = "https://www.reddit.com/user/brendt_gd/submitted.json?limit=50&after={$after}";

            $output->writeln("Fetching from {$url}");

            $data = json_decode(file_get_contents($url), true)['data'];

            $after = $data['after'] ?? null;

            $children = array_map(
                fn(array $item) => $item['data'],
                $data['children'] ?? []
            );

            $submissions = [...$submissions, ...$children];

            $output->writeln('Current count ' . count($submissions));
        } while ($after !== null);

        file_put_contents($cachePath, json_encode($submissions));

        return $submissions;
    }
}

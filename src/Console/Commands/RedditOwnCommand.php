<?php

namespace Brendt\Stitcher\Console\Commands;

use Brendt\Stitcher\Console\DTO\RedditPost;
use Brendt\Stitcher\Console\RedditRepository;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Stitcher\App;
use Stitcher\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class RedditOwnCommand extends Command
{
    private RedditRepository $repository;

    public function __construct(RedditRepository $repository)
    {
        parent::__construct('reddit:own');

        $this->repository = $repository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $submissions = $this->repository->all($output)
            ->filter(fn(RedditPost $submission) => $submission->domain === 'stitcher.io')
            ->groupBy(fn(RedditPost $submission) => $submission->url)
            ->map(function (Collection $items) {
                return $items->sortBy(fn(RedditPost $submission) => $submission->date);
            });

        $posts = $this->getPosts()
            ->sortBy(function (array $post) use ($submissions) {
                $url = "/blog/{$post['id']}";

                if (! isset($submissions[$url])) {
                    return (new DateTimeImmutable(date('Y-m-d H:i', $post['date'])))->getTimestamp();
                }

                return $submissions[$url]->last()->date->getTimestamp();
            });

        $table = new Table($output);

        $table->setHeaders([
            'URL',
            'Subreddit',
            'Title',
            'Score',
            'Date',
        ]);

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

            /** @var \Brendt\Stitcher\Console\DTO\RedditPost $submission */
            foreach ($submissionsForPost as $submission) {
                $row = [
                    $isFirstForUrl ? $url : '',
                    $submission->subreddit,
                    $submission->formattedTitle(),
                    $submission->formattedScore(),
                    $submission->formattedDate(),
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

    private function getPosts(): Collection
    {
        /** @var \Symfony\Component\Yaml\Yaml $yaml */
        $yaml = App::get(Yaml::class);

        $posts = $yaml->parse(file_get_contents(File::path('src/content/blog.yaml')));

        return collect($posts)
            ->map(function (array $post, string $id) {
                $post['id'] = $id;

                return $post;
            });
    }
}

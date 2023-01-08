<?php

namespace Brendt\Stitcher\Console\Commands;

use Brendt\Stitcher\Console\DTO\RedditPost;
use Brendt\Stitcher\Console\RedditRepository;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RedditWeekCommand extends Command
{
    private RedditRepository $repository;

    public function __construct(RedditRepository $repository)
    {
        parent::__construct('reddit:week');

        $this->repository = $repository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $posts = $this->repository->all($output)
            ->groupBy(fn(RedditPost $post) => $post->subreddit)
            ->map(function (Collection $postsPerSub) {
                return $postsPerSub
                    ->groupBy(fn(RedditPost $post) => $post->date->format('l'))
                    ->map(function (Collection $posts) {
                        return [
                            'score' => $posts->sum(fn(RedditPost $post) => $post->score),
                        ];
                    })
                    ->sortBy(fn(array $item) => $item['score']);
            })
            ->sortBy(function (Collection $postsPerSub) {
                return $postsPerSub->sum(fn (array $item) => $item['score']);
            });


        $table = new Table($output);

        $table->setHeaders(['Subreddit', 'Weekday', 'Upvotes']);

        $rows = [];


        foreach ($posts as $subReddit => $postsPerSub) {
            $isFirstForUrl = true;

            foreach ($postsPerSub as $weekday => $item) {
                $rows[] = [
                    $isFirstForUrl ? $subReddit : '',
                    $weekday,
                    $item['score'],
                ];

                $isFirstForUrl = false;
            }

            $rows[] = new TableSeparator();
        }

        unset($rows[array_key_last($rows)]);

        $table->setRows($rows);
        $table->render();

        return 0;
    }
}

<?php

namespace Brendt\Stitcher\Console\Commands;

use Brendt\Stitcher\Console\DTO\RedditPost;
use Brendt\Stitcher\Console\RedditRepository;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RedditSubStatusCommand extends Command
{
    private RedditRepository $repository;

    public function __construct(RedditRepository $repository)
    {
        parent::__construct('reddit:sub-status');

        $this->addOption('--clean', null, InputOption::VALUE_OPTIONAL, '', false);
        $this->repository = $repository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $submissions = $this->repository->all($input, $output)
            ->groupBy(fn(RedditPost $submission) => $submission->subreddit)
            ->map(function (Collection $items) {
                $ownCount = $items
                    ->filter(fn(RedditPost $submission) => $submission->isOwn)
                    ->count();

                $allCount = $items->count();

                return [
                    'allCount' => $allCount,
                    'ownCount' => $ownCount,
                    'ownRatio' => round($ownCount / $allCount, 2) * 100,
                    'totalScore' => $items->sum(fn(RedditPost $submission) => $submission->score),
                ];
            })
            ->filter(fn(array $item) => $item['allCount'] > 2)
            ->sortBy(fn(array $item) => $item['allCount']);

        $table = new Table($output);

        $table->setHeaders([
            'Subreddit',
            'own/all',
            'Total score'
        ]);

        $rows = [];

        foreach ($submissions as $subreddit => $data) {
            $rows[] = [
                $subreddit,
                "{$data['ownCount']}/{$data['allCount']} ({$data['ownRatio']})%",
                $data['totalScore'],
            ];
        }

        $table->setRows($rows);

        $table->render();
    }
}

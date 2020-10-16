<?php

namespace Brendt\Stitcher\Console\Commands;

use Brendt\Stitcher\Console\RedditRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RedditSyncCommand extends Command
{
    private RedditRepository $repository;

    public function __construct(RedditRepository $repository)
    {
        parent::__construct('reddit:sync');

        $this->repository = $repository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->repository->sync($output);
    }
}

<?php

namespace Brendt\Stitcher\Console\Commands;

use Brendt\Stitcher\Console\TwitterRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TwitterSyncCommand extends Command
{
    private TwitterRepository $repository;

    public function __construct(TwitterRepository $twitterRepository)
    {
        parent::__construct('twitter:sync');

        $this->repository = $twitterRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->repository->sync($output);
    }
}

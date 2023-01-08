<?php

namespace Brendt\Stitcher\Console\Commands;

use Brendt\Stitcher\Console\BlogRepository;
use Brendt\Stitcher\Console\DTO\BlogPost;
use Brendt\Stitcher\Console\DTO\Tweet;
use Brendt\Stitcher\Console\TwitterRepository;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TwitterOwnCommand extends Command
{
    private TwitterRepository $twitterRepository;

    private BlogRepository $blogRepository;

    public function __construct(
        TwitterRepository $twitterRepository,
        BlogRepository $blogRepository
    ) {
        parent::__construct('twitter:own');

        $this->twitterRepository = $twitterRepository;
        $this->blogRepository = $blogRepository;

        $this->addOption('top', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tweetsPerUrl = $this->twitterRepository
            ->all($output)
            ->filter(fn(Tweet $tweet) => $tweet->ownUrl !== null)
            ->groupBy(fn(Tweet $tweet) => $tweet->ownUrl)
            ->map(
                fn(Collection $tweets) => $tweets->sortBy(
                    function (Tweet $tweet) use ($input) {
                        if ($input->getOption('top')) {
                            return $tweet->likes;
                        }

                        return $tweet->date->getTimestamp();
                    }
                )
            );

        $posts = $this->blogRepository
            ->all()
            ->filter(function (BlogPost $post) use ($tweetsPerUrl) {
                $tweetsForUrl = $tweetsPerUrl[$post->url] ?? null;

                return $tweetsForUrl !== null;
            })
            ->sortBy(function (BlogPost $post) use ($input, $tweetsPerUrl) {
                $tweetsForUrl = $tweetsPerUrl[$post->url];

                /** @var \Brendt\Stitcher\Console\DTO\Tweet $tweet */
                $tweet = $tweetsForUrl->last();

                if ($input->getOption('top')) {
                    return $tweet->likes;
                }

                return $tweet->date->getTimestamp();
            });

        $table = new Table($output);

        $table->setHeaders([
            'URL',
            'Tweet',
            'Date',
            'Likes',
            'Retweets',
        ]);

        $count = 0;

        $rows = [];

        foreach ($posts as $post) {
            $isFirstForUrl = true;

            $tweetsForPost = $tweetsPerUrl[$post->url] ?? [];

            if (! count($tweetsForPost)) {
                $rows[] = [$post->url];

                $rows[] = new TableSeparator();

                continue;
            }

            /** @var \Brendt\Stitcher\Console\DTO\Tweet $tweet */
            foreach ($tweetsForPost as $tweet) {
                $row = [
                    $isFirstForUrl ? $post->url : '',
                    $tweet->formattedDescription(),
                    $tweet->formattedDate(),
                    $tweet->formattedLikes(),
                    $tweet->formattedRetweets(),
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

        return 0;
    }
}

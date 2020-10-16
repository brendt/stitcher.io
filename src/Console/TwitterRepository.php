<?php

namespace Brendt\Stitcher\Console;

use Brendt\Stitcher\Console\DTO\Tweet;
use DG\Twitter\Twitter;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Output\OutputInterface;

class TwitterRepository
{
    private const CACHE_PATH = __DIR__ . '/Data/tweets.json';

    private Twitter $twitter;

    public function __construct(
        string $consumerKey,
        string $consumerSecret,
        string $accessToken,
        string $accessTokenSecret
    ) {
        $this->twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \Illuminate\Support\Collection|\Brendt\Stitcher\Console\DTO\Tweet[]
     */
    public function all(OutputInterface $output): Collection
    {

        if (file_exists(self::CACHE_PATH)) {
            return $this->asCollection(json_decode(file_get_contents(self::CACHE_PATH)));
        }

        return $this->sync($output);
    }

    public function sync(OutputInterface $output): Collection
    {
        $tweets = [];

        $maxId = null;

        $iterations = 0;

        $maxIterations = 20;

        do {
            $currentTweets = $this->twitter->request('statuses/user_timeline', 'GET', ['count' => 200, 'max_id' => $maxId, 'exclude_replies' => true]);

            $lastStatus = array_last($currentTweets);

            $maxId = $lastStatus->id;

            $tweets = [...$tweets, ...$currentTweets];

            $iterations++;

            $output->writeln("[{$iterations}/{$maxIterations}] Current count " . count($tweets));
        } while ($maxId !== null && $iterations < $maxIterations);

        file_put_contents(self::CACHE_PATH, json_encode($tweets));

        return $this->asCollection($tweets);
    }

    private function asCollection(array $items): Collection
    {
        return collect($items)->map(fn(object $item) => Tweet::make($item));
    }
}

<?php

namespace Brendt\Stitcher\Console;

use Brendt\Stitcher\Console\DTO\RedditPost;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RedditRepository
{
    private const CACHE_PATH = __DIR__ . '/Data/reddit.json';

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \Illuminate\Support\Collection|\Brendt\Stitcher\Console\DTO\RedditPost[]
     */
    public function all(InputInterface $input, OutputInterface $output): Collection
    {
        if ($input->getOption('clean') === false && file_exists(self::CACHE_PATH)) {
            return $this->asCollection(json_decode(file_get_contents(self::CACHE_PATH), true));
        }

        return $this->sync($output);
    }

    public function sync(OutputInterface $output): Collection
    {
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

        file_put_contents(self::CACHE_PATH, json_encode($submissions));

        return $this->asCollection($submissions);
    }

    private function asCollection(array $items): Collection
    {
        return collect($items)->map(fn(array $item) => RedditPost::make($item));
    }
}

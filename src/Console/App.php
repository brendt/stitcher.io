<?php

namespace Brendt\Stitcher\Console;

use Brendt\Stitcher\Console\Commands\RedditOwnCommand;
use Brendt\Stitcher\Console\Commands\RedditSubStatusCommand;
use Brendt\Stitcher\Console\Commands\RedditSyncCommand;
use Brendt\Stitcher\Console\Commands\TwitterOwnCommand;
use Brendt\Stitcher\Console\Commands\TwitterSyncCommand;
use Symfony\Component\Console\Application;

class App extends Application
{
    public function __construct()
    {
        parent::__construct('Console', 1);

        $redditDataRepository = new RedditRepository();

        $twitterDataRepository = new TwitterRepository(
            getenv('TWITTER_API_KEY'),
            getenv('TWITTER_API_SECRET_KEY'),
            getenv('TWITTER_ACCESS_TOKEN'),
            getenv('TWITTER_ACCESS_TOKEN_SECRET')
        );

        $blogRepostiory = new BlogRepository();

        $this->addCommands([
            new RedditOwnCommand($redditDataRepository),
            new RedditSubStatusCommand($redditDataRepository),
            new RedditSyncCommand($redditDataRepository),
            new TwitterSyncCommand($twitterDataRepository),
            new TwitterOwnCommand($twitterDataRepository, $blogRepostiory),
        ]);
    }
}

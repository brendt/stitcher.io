<?php

namespace Brendt\Stitcher\Console;

use Symfony\Component\Console\Application;

class App extends Application
{
    public function __construct()
    {
        parent::__construct('Console', 1);

        $this->addCommands([
            new RedditAnalysisCommand(),
        ]);
    }
}

<?php

namespace App\Nws\Commands;

use App\Nws\Nws;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;
use function Codewithkyrian\Transformers\Pipelines\pipeline;

final class NwsSentimentCommand
{
    use HasConsole;

    #[ConsoleCommand, Schedule(Every::DAY)]
    public function __invoke(bool $all = false): void
    {
        error_reporting(E_ALL ^ E_DEPRECATED);

        $classifier = pipeline('sentiment-analysis', 'twn39/multilingual-sentiment-analysis-ONNX');

        $query = Nws::select();

        if (! $all) {
            $query->whereNull('sentiment');
        }

        $query->chunk(function (array $items) use ($classifier) {
            /** @var Nws $nws */
            foreach ($items as $nws) {
                $sentiment = $classifier($nws->title . ' ' . $nws->summary);
                $nws->sentiment = $sentiment;
                $nws->save();
                $this->success($nws->title);
            }
        });

    }
}
<?php

namespace App\Nws\Commands;

use App\Nws\Nws;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;
use function Codewithkyrian\Transformers\Pipelines\pipeline;

final class NwsKeywordsCommand
{
    use HasConsole;

    #[ConsoleCommand, Schedule(Every::DAY)]
    public function __invoke(bool $all = false): void
    {
//        error_reporting(E_ALL ^ E_DEPRECATED);

//        $classifier = pipeline('zero-shot-classification', 'MoritzLaurer/mDeBERTa-v3-base-xnli-multilingual-nli-2mil7');
//        $categories = ['politiek', 'oorlog', 'cultuur', 'klimaat', 'ongeval', 'ramp', 'tech',];

        $pipeline = pipeline('summarization');

        $query = Nws::select();

        if (! $all) {
            $query->whereNull('keywords');
        }

        $query->chunk(function (array $items) use ($pipeline) {
            /** @var Nws $nws */
            foreach ($items as $nws) {
                $keywords = $pipeline($nws->summary);
ld($keywords);
                $matchedKeywords = [];

                foreach ($keywords['scores'] as $i => $score) {
                    $matchedKeywords[] = [
                        'label' => $keywords['labels'][$i],
                        'score' => $score,
                    ];
                }

                $nws->keywords = $matchedKeywords;
                $nws->save();

                if ($nws->keywords !== []) {
                    $this->success($nws->title);
                } else {
                    $this->error($nws->title);
                }
            }
        });

    }
}
<?php

namespace App\Nws\Commands;

use App\Nws\Nws;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;
use Throwable;
use function Codewithkyrian\Transformers\Pipelines\pipeline;

final class NwsKeywordsCommand
{
    use HasConsole;

    #[ConsoleCommand, Schedule(Every::DAY)]
    public function __invoke(bool $all = false): void
    {
        error_reporting(E_ALL ^ E_DEPRECATED);


//        $classifier = pipeline('zero-shot-classification');
//        $summarizer = pipeline('summarization', 'Xenova/distilbart-cnn-6-6');
//        $keywords = ['politics', 'public health', 'economics', 'elections', 'climate', 'holiday', 'accident', 'airlines', 'tech'];

        $generator = pipeline('text-generation', 'Xenova/TinyLlama-1.1B-Chat-v1.0');

        $query = Nws::select();

        if (! $all) {
            $query->whereNull('keywords');
        }

        $query->chunk(function (array $items) use ($generator) {
            /** @var Nws $nws */
            foreach ($items as $nws) {
                try {
                    $messages = [
                        ['role' => 'system', 'content' => 'find keywords within the user provided text. Always output three keywords, separated by colons, nothing else.'],
                        ['role' => 'user', 'content' => strtolower($nws->title . ' ' . $nws->summary)],
                    ];

                    $keywords = $generator($messages);

                    ld($keywords);
//                    $result = $classifier($summary[0]['summary_text'], $keywords);
                } catch (Throwable) {
                    $this->error($nws->title);
                    continue;
                }


                ld($result);

                $matchedKeywords = [];

                foreach ($result['scores'] as $i => $score) {
                    if ($score < 0.3) {
                        continue;
                    }

                    $matchedKeywords[] = [
                        'label' => $result['labels'][$i],
                        'score' => $score,
                    ];
                }

                $nws->keywords = $matchedKeywords ?: null;
                $nws->save();

                if ($nws->keywords !== null) {
                    $this->success($nws->title);
                } else {
                    $this->error($nws->title);
                }
            }
        });

    }
}
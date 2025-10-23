<?php

namespace App\PhpDocs;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use function Tempest\Support\Filesystem\delete;
use function Tempest\Support\str;

final class DocsParseCommand
{
    use HasConsole;

    public function __construct(
        private DocBookParser $parser,
    ) {}

    #[ConsoleCommand]
    public function __invoke(?string $filter = null): void
    {
        $outputBase = __DIR__ . '/md';

        if (is_dir($outputBase)) {
            delete($outputBase);
        }

        $inputBase = __DIR__ . '/xml/language';
        if ($filter) {
            $files = glob($inputBase . '/' . $filter);
        } else {
            $files = glob($inputBase . '/**/*.xml');
        }

        $success = 0;
        $failed = 0;

        foreach ($files as $inputPath) {
            $parsed = $this->parser->parse($inputPath);

            if (! $parsed) {
                $this->error($inputPath);
                $failed++;
                continue;
            }

            $outputPath = str($inputPath)->replace($inputBase, '')->prepend($outputBase)->replaceEnd('.xml', '.md');

            if (! is_dir(dirname($outputPath))) {
                mkdir(dirname($outputPath), recursive: true);
            }

            file_put_contents($outputPath, $parsed);

            $success++;
            $this->success($outputPath);
        }

        $this->info("Parsed {$success} files, {$failed} failed");
    }
}
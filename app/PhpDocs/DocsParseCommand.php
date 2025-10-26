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
        private readonly PhpDocsParser $parser,
    ) {}

    #[ConsoleCommand]
    public function __invoke(?string $filter = null): void
    {
        $outputBase = __DIR__ . '/md/';

        if (is_dir($outputBase) && $filter === null) {
            delete($outputBase);
        }

        $inputBase = __DIR__ . '/xml/';

        if ($filter) {
            $files = glob($inputBase . $filter . '*.xml');
        } else {
            $files = glob($inputBase . '{,*/,*/*/,*/*/*/}*.xml', GLOB_BRACE);
        }

        $success = 0;
        $failed = 0;

        foreach ($files as $inputPath) {
            $slug = str_replace([$inputBase, '.xml'], '', $inputPath);

            $parsed = $this->parser->parse($slug, $inputPath);

            if (! $parsed) {
                $this->error($inputPath);
                $failed++;
                continue;
            }

            $outputPath = str($inputPath)->replace($inputBase, '')->prepend($outputBase)->replaceEnd('.xml', '.md');

            if (! is_dir(dirname($outputPath))) {
                mkdir(dirname($outputPath), recursive: true);
            }

            $this->index(
                $outputPath->replaceStart($outputBase, '/')->replaceEnd('.md', ''),
                $parsed,
            );

            file_put_contents($outputPath, $parsed);

            $success++;
            $this->success($outputPath);
        }

        $this->info("Parsed {$success} files, {$failed} failed");
    }

    private function index(string $slug, string $parsed): void
    {
        $title = str($parsed)->afterFirst('<h1>')->before('</h1>')->before(PHP_EOL)->trim()->toString();

        Index::updateOrCreate(
            [
                'uri' => ltrim($slug, '/'),
            ],
            [
                'title' => $title,
            ],
        );
    }
}
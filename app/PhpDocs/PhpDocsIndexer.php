<?php

namespace App\PhpDocs;

use App\Support\CommandPalette\Command;
use App\Support\CommandPalette\Indexer;
use App\Support\CommandPalette\Type;
use Tempest\Support\Arr\ImmutableArray;
use function Tempest\Router\uri;
use function Tempest\Support\arr;

final class PhpDocsIndexer implements Indexer
{
    public function index(): ImmutableArray
    {
        $basePath = __DIR__ . '/md/';

        return arr(glob($basePath . '{,*/,*/*/,*/*/*/}*.md', GLOB_BRACE))
            ->map(function (string $path) use ($basePath) {
                $slug = str_replace([$basePath, '.md'], '', $path);

                return new Command(
                    title: $slug,
                    type: Type::URI,
                    hierarchy: explode('/', $slug),
                    uri: uri([PhpDocsController::class, 'page'], slug: $slug),
                    fields: [],
                );
            });

    }
}
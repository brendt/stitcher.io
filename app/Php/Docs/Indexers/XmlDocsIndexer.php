<?php

namespace App\Php\Docs\Indexers;

use App\Php\Docs\DocsController;
use App\Php\Search\Index;
use App\Php\Search\Indexer;
use function Tempest\Router\uri;
use function Tempest\Support\str;

final class XmlDocsIndexer implements Indexer
{
    public function index(): void
    {
        $xmlBase = __DIR__ . '/../xml/';
        $mdBase = __DIR__ . '/../md/';

        $files = glob($xmlBase . '{,*/,*/*/,*/*/*/}*.xml', GLOB_BRACE);

        foreach ($files as $path) {
            if (file_exists(str_replace([$xmlBase, '.xml'], [$mdBase, '.md'], $path))) {
                continue;
            }

            $content = str(file_get_contents($path));

            if (! $content->contains('xml:id="')) {
                continue;
            }

            $title = str($path)->afterLast('/')->before('.xml')->trim()->title()->toString();
            $slug = $content->afterFirst('xml:id="')->before('"')->trim()->toString();

            Index::updateOrCreate(
                [
                    'uri' => 'https://php.net/manual/en/' . $slug . '.php'
                ],
                [
                    'priority' => 100,
                    'title' => $title,
                ],
            );
        }
    }
}
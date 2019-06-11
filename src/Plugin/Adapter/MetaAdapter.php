<?php

namespace Brendt\Stitcher\Plugin\Adapter;

use Stitcher\Page\Adapter;

class MetaAdapter implements Adapter
{
    public function transform(array $pageConfiguration): array
    {
        if ($pageConfiguration['variables']['post']['disableMetaDescription'] ?? null) {
            unset($pageConfiguration['variables']['meta']['description']);
        }

        return [$pageConfiguration];
    }
}

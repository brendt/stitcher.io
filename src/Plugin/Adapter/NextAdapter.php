<?php

namespace Brendt\Stitcher\Plugin\Adapter;

use Stitcher\File;
use Stitcher\Page\Adapter;
use Symfony\Component\Yaml\Yaml;

class NextAdapter implements Adapter
{
    public function transform(array $pageConfiguration): array
    {
        if (isset($pageConfiguration['variables']['post']['next'])) {
            $pageConfiguration['variables']['post']['next'] = $this->parseNext($pageConfiguration);
        }

        return [$pageConfiguration['id'] => $pageConfiguration];
    }

    private function parseNext(array $pageConfiguration): array
    {
        $nextId = $pageConfiguration['variables']['post']['next'];

        $blogData = Yaml::parse(File::read('src/content/blog.yaml'));

        if (! isset($blogData[$nextId])) {
            return [];
        }

        return [
            'title' => $blogData[$nextId]['title'],
            'id' => $nextId,
        ];
    }
}

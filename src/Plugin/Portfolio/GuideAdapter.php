<?php

namespace Brendt\Stitcher\Plugin\Portfolio;

use Stitcher\Exception\InvalidConfiguration;
use Stitcher\Page\Adapter;
use Stitcher\Variable\VariableParser;

class GuideAdapter implements Adapter
{
    /** @var string */
    private $variableName;

    /** @var \Stitcher\Variable\VariableParser */
    private $variableParser;

    public function __construct(
        array $configuration,
        VariableParser $variableParser
    ) {
        if (!$configuration['variable'] ?? null) {
            throw InvalidConfiguration::invalidAdapterConfiguration('guide', 'variables');
        }

        $this->variableName = $configuration['variable'];
        $this->variableParser = $variableParser;
    }

    public function transform(array $pageConfiguration): array
    {
        $variable = $pageConfiguration['variables'][$this->variableName] ?? null;

        $guidePages = $this->variableParser->parse($variable);

        $menu = [];

        foreach ($guidePages as $id => $guidePage) {
            $category = $guidePage['category'] ?? 0;

            $menu[$category][$id] = $guidePage;
        }

        $pageConfiguration['variables']['menu'] = $menu;

        unset($pageConfiguration['config']['guide']);

        return [$pageConfiguration];
    }
}

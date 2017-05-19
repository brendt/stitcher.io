<?php

namespace Brendt\Stitcher\Plugin\Portfolio;

use Brendt\Stitcher\Adapter\AbstractAdapter;
use Brendt\Stitcher\Site\Page;

class GuideAdapter extends AbstractAdapter
{
    /**
     * @param Page        $page
     * @param string|null $filter
     *
     * @return Page[]
     */
    public function transformPage(Page $page, $filter = null) : array {
        $guidePages = $this->getData($page->getVariable('pages'));
        $menu = [];

        foreach ($guidePages as $id => $guidePage) {
            $category = $guidePage['category'] ?? 0;
            $menu[$category][$id] = $guidePage;
        }

        $page->setVariableValue('menu', $menu)
            ->setVariableIsParsed('menu');

        return [$page];
    }
}

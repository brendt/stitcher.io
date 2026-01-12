<?php

namespace App\Aggregate\Posts;

use Tempest\View\View;
use function Tempest\View\view;

final class SourcesListController
{
    private function render(): View
    {
        return \Tempest\View\view('x-sources-list.view.php');
    }
}
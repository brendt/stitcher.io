<?php

namespace App\Aggregate\Posts;

use Tempest\View\View;
use function Tempest\view;

final class SourcesListController
{
    private function render(): View
    {
        return view('x-sources-list.view.php');
    }
}
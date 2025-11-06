<?php

namespace App\Talks;

use Tempest\Router\Get;
use Tempest\Router\StaticPage;
use Tempest\View\View;

final class TalksController
{
    #[StaticPage]
    #[Get('/things-talk')]
    public function things(): View
    {
        return \Tempest\view(
            'things-talk.view.php',
        );
    }
}
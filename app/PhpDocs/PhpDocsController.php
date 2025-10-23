<?php

namespace App\PhpDocs;

use Tempest\Http\Responses\NotFound;
use Tempest\Router\Get;
use Tempest\View\View;

final class PhpDocsController
{
    #[Get('/php/{slug:.*}')]
    public function page(string $slug): View|NotFound
    {
        $markdown = @file_get_contents(__DIR__ . '/md/' . $slug . '.md');

        if (! $markdown) {
            return new NotFound();
        }

        return \Tempest\view('php-docs.view.php', content: $markdown);
    }
}
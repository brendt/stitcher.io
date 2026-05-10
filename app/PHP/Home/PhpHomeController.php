<?php

namespace App\Php\Home;

use League\CommonMark\MarkdownConverter;
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class PhpHomeController
{
    #[Get('/php')]
    public function index(MarkdownConverter $markdown): View
    {
        $snippet = $markdown->convert(file_get_contents(__DIR__ . '/Snippets/home-01.md'))->getContent();

        return view(
            'php-home.view.php',
            snippet: $snippet,
        );
    }
}
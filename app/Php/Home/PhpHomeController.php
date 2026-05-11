<?php

namespace App\Php\Home;

use App\Blog\Meta;
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
            meta: new Meta(
                title: 'PHP — The General-Purpose Language for the Web',
                description: 'PHP is a powerful, general-purpose programming language for web and console development. It powers 43% of the web, from personal blogs to platforms serving billions of users.',
                canonical: 'https://stitcher.io/php',
            ),
        );
    }
}
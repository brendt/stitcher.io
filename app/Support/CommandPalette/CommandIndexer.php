<?php

namespace App\Support\CommandPalette;

use Override;
use App\Web\Blog\BlogController;
use App\Web\Documentation\DocumentationController;
use App\Web\RedirectsController;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Router\uri;

final readonly class CommandIndexer implements Indexer
{
    #[Override]
    public function index(): ImmutableArray
    {
        return new ImmutableArray([
            new Command(
                title: 'Read the documentation',
                type: Type::URI,
                hierarchy: ['Commands', 'Documentation'],
                uri: uri([DocumentationController::class, 'index']),
            ),
            new Command(
                title: 'Visit the blog',
                type: Type::URI,
                hierarchy: ['Commands', 'Blog'],
                uri: uri([BlogController::class, 'index']),
            ),
            new Command(
                title: 'See the code on GitHub',
                type: Type::URI,
                hierarchy: ['Commands', 'Link'],
                uri: uri([RedirectsController::class, 'github']),
            ),
            new Command(
                title: 'Join our Discord server',
                type: Type::URI,
                hierarchy: ['Commands', 'Link'],
                uri: uri([RedirectsController::class, 'discord']),
            ),
            new Command(
                title: 'Follow me on Bluesky',
                type: Type::URI,
                hierarchy: ['Commands', 'Link'],
                uri: uri([RedirectsController::class, 'bluesky']),
            ),
            new Command(
                title: 'Follow me on X',
                type: Type::URI,
                hierarchy: ['Commands', 'Link'],
                uri: uri([RedirectsController::class, 'twitter']),
            ),
            new Command(
                title: 'Toggle dark mode',
                type: Type::JAVASCRIPT,
                hierarchy: ['Commands', 'Command'],
                javascript: 'toggleDarkMode',
            ),
        ]);
    }
}

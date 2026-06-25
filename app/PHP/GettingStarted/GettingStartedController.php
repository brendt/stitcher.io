<?php

namespace App\PHP\GettingStarted;

use Tempest\Http\Response;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;
use Tempest\Router\Prefix;
use Tempest\View\View;
use function Tempest\View\view;

#[Prefix('/php')]
final readonly class GettingStartedController
{
    public function __construct(
        private GettingStartedRepository $repository,
    ) {}

    #[Get('/')]
    public function index(): Response
    {
        /** @var \App\PHP\GettingStarted\GettingStartedPage|null $first */
        $first = $this->repository->all()[0] ?? null;

        if (! $first) {
            return new NotFound();
        }

        return new Redirect($first->uri);
    }

    #[Get('/{slug}')]
    public function show(string $slug): Response|View
    {
        $page = $this->repository->find($slug);

        if (! $page) {
            return new NotFound();
        }

        $pages = $this->repository->all();

        return view('getting-started.view.php', page: $page, pages: $pages);
    }
}
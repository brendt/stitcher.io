<?php

namespace App\Todo;

use App\Support\Authentication\Admin;
use App\Support\Authentication\User;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Http\Response;
use Tempest\Http\Responses\NotFound;
use Tempest\Router\Get;
use Tempest\Router\Prefix;
use Tempest\View\View;

use function Tempest\View\view;

#[Prefix('/todo'), Admin]
final readonly class TodoController
{
    public function __construct(
        private Authenticator $authenticator,
        private TodoMarkdown $markdown,
    ) {}

    #[Get('/')]
    public function index(): View|Response
    {
        return $this->render(null);
    }

    #[Get('/{workspaceId}')]
    public function show(int $workspaceId): View|Response
    {
        return $this->render($workspaceId);
    }

    private function render(?int $workspaceId): View|Response
    {
        /** @var User $user */
        $user = $this->authenticator->current();

        $workspaces = Workspace::select()
            ->with('projects.tasks')
            ->where('workspaces.user_id = ?', $user->id->value)
            ->orderBy('workspaces.id ASC, projects.id ASC, projects_tasks.id ASC')
            ->all();

        $workspace = $workspaceId === null
            ? $workspaces[0] ?? null
            : array_find($workspaces, fn (Workspace $workspace) => $workspace->id->value === $workspaceId);

        if ($workspaceId !== null && ! $workspace) {
            return new NotFound();
        }

        return view(
            'todo.view.php',
            workspaces: $workspaces,
            workspace: $workspace,
            markdown: $workspace ? $this->markdown->render($workspace) : '',
        );
    }
}

<?php

namespace App\Todo;

use App\Support\Authentication\Admin;
use App\Support\Authentication\User;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\DateTime\DateTime;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\Post;
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
    public function index(Request $request): View|Response
    {
        return $this->render(null, $this->filter($request));
    }

    #[Get('/{workspaceId}')]
    public function show(int $workspaceId, Request $request): View|Response
    {
        return $this->render($workspaceId, $this->filter($request));
    }

    #[Post('/projects/{projectId}/complete')]
    public function completeProject(int $projectId): Response
    {
        return $this->updateProject($projectId, DateTime::now());
    }

    #[Post('/projects/{projectId}/reopen')]
    public function reopenProject(int $projectId): Response
    {
        return $this->updateProject($projectId, null);
    }

    #[Post('/tasks/{taskId}/complete')]
    public function completeTask(int $taskId): Response
    {
        return $this->updateTask($taskId, DateTime::now());
    }

    #[Post('/tasks/{taskId}/reopen')]
    public function reopenTask(int $taskId): Response
    {
        return $this->updateTask($taskId, null);
    }

    private function updateProject(int $projectId, ?DateTime $completedAt): Response
    {
        /** @var Project|null $project */
        $project = Project::select()
            ->where('projects.id = ?', $projectId)
            ->where('projects.workspace_id IN (SELECT workspaces.id FROM workspaces WHERE workspaces.user_id = ?)', $this->user()->id->value)
            ->first();

        if (! $project) {
            return new NotFound();
        }

        $project->update(completedAt: $completedAt);

        return new Ok();
    }

    private function updateTask(int $taskId, ?DateTime $completedAt): Response
    {
        /** @var Task|null $task */
        $task = Task::select()
            ->where('tasks.id = ?', $taskId)
            ->where(
                'tasks.project_id IN (SELECT projects.id FROM projects INNER JOIN workspaces ON workspaces.id = projects.workspace_id WHERE workspaces.user_id = ?)',
                $this->user()->id->value,
            )
            ->first();

        if (! $task) {
            return new NotFound();
        }

        $task->update(completedAt: $completedAt);

        return new Ok();
    }

    private function render(?int $workspaceId, ?string $filter): View|Response
    {
        $workspaces = Workspace::select()
            ->with('projects.tasks')
            ->where('workspaces.user_id = ?', $this->user()->id->value)
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
            filter: $filter,
            markdown: $workspace ? $this->markdown->render($workspace, $filter) : '',
            items: $workspace ? $this->markdown->items($workspace, $filter) : [],
        );
    }

    private function filter(Request $request): ?string
    {
        $filter = $request->get('filter');

        return is_string($filter) && TodoMarkdown::isTag($filter) ? $filter : null;
    }

    private function user(): User
    {
        /** @var User */
        return $this->authenticator->current();
    }
}

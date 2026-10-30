<?php

namespace App\Todo;

use App\Support\Authentication\Admin;
use App\Support\Authentication\User;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\DateTime\DateTime;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Json;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\ServerError;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\Prefix;
use Tempest\View\View;

use function Tempest\Router\uri;
use function Tempest\View\view;

#[Prefix('/todo'), Admin]
final readonly class TodoController
{
    public function __construct(
        private Authenticator $authenticator,
        private TodoMarkdown $markdown,
        private TodoSync $sync,
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

    #[Post('/{workspaceId}/save')]
    public function save(int $workspaceId, Request $request): Response
    {
        /** @var Workspace|null $workspace */
        $workspace = Workspace::select()
            ->with('projects.tasks')
            ->where('workspaces.id = ?', $workspaceId)
            ->where('workspaces.user_id = ?', $this->user()->id->value)
            ->first();

        if (! $workspace) {
            return new NotFound();
        }

        $ids = $this->sync->sync($workspace, $request->body);

        if ($ids === null) {
            return new ServerError();
        }

        return new Json(['ids' => (object) $ids]);
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

    /**
     * @param string[] $filters
     */
    private function render(?int $workspaceId, array $filters): View|Response
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
            filters: $filters,
            filterLinks: $workspace ? $this->filterLinks($workspace, $filters) : [],
            markdown: $workspace ? $this->markdown->render($workspace, $filters) : '',
            items: $workspace ? $this->markdown->items($workspace, $filters) : [],
            tags: $this->markdown->tags(...$workspaces),
        );
    }

    /**
     * Multiple hashtags or mentions are separated by spaces, for example `?filter=#urgent @alex`.
     *
     * @return string[]
     */
    private function filter(Request $request): array
    {
        $filter = $request->get('filter');

        if (! is_string($filter)) {
            return [];
        }

        $filters = [];

        foreach (preg_split('/\s+/', $filter, flags: PREG_SPLIT_NO_EMPTY) ?: [] as $tag) {
            if (! TodoMarkdown::isTag($tag)) {
                continue;
            }

            $filters[mb_strtolower($tag)] ??= $tag;
        }

        return array_values($filters);
    }

    /**
     * For every active filter, the URL that shows the workspace without it.
     *
     * @param string[] $filters
     * @return array<string, string>
     */
    private function filterLinks(Workspace $workspace, array $filters): array
    {
        $links = [];

        foreach ($filters as $filter) {
            $remaining = array_filter($filters, fn (string $other) => $other !== $filter);

            $links[$filter] = $this->filterUri($workspace, $remaining);
        }

        return $links;
    }

    /**
     * @param string[] $filters
     */
    private function filterUri(Workspace $workspace, array $filters): string
    {
        $uri = uri([self::class, 'show'], workspaceId: $workspace->id);

        return $filters === [] ? $uri : $uri . '?filter=' . rawurlencode(implode(' ', $filters));
    }

    private function user(): User
    {
        /** @var User */
        return $this->authenticator->current();
    }
}

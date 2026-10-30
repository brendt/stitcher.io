<?php

namespace App\Todo;

use App\Support\Authentication\Admin;
use App\Support\Authentication\User;
use Closure;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\FormatPattern;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Forbidden;
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

    /**
     * Archived projects of all workspaces, most recently archived first. They can be unarchived, but not edited.
     */
    #[Get('/archived')]
    public function archived(Request $request): View
    {
        $filters = $this->filter($request);
        $workspaces = $this->workspaces();
        $projects = [];

        foreach ($workspaces as $workspace) {
            foreach ($workspace->projects as $project) {
                if ($project->archivedAt === null) {
                    continue;
                }

                $projects[] = $project;
            }
        }

        usort(
            $projects,
            fn (Project $a, Project $b) => ($b->archivedAt?->format(FormatPattern::SQL_DATE_TIME) ?? '') <=> ($a->archivedAt?->format(FormatPattern::SQL_DATE_TIME) ?? ''),
        );

        return $this->view(
            workspaces: $workspaces,
            workspace: null,
            projects: $projects,
            filters: $filters,
            baseUri: uri([self::class, 'archived']),
            isArchive: true,
        );
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
        return $this->updateProject($projectId, fn (Project $project) => $project->update(completedAt: DateTime::now()));
    }

    #[Post('/projects/{projectId}/reopen')]
    public function reopenProject(int $projectId): Response
    {
        return $this->updateProject($projectId, fn (Project $project) => $project->update(completedAt: null));
    }

    #[Post('/projects/{projectId}/archive')]
    public function archiveProject(int $projectId): Response
    {
        return $this->updateProject($projectId, function (Project $project): ?Response {
            // Only completed projects can be archived
            if ($project->completedAt === null) {
                return new Forbidden();
            }

            $project->update(archivedAt: DateTime::now());

            return null;
        });
    }

    #[Post('/projects/{projectId}/unarchive')]
    public function unarchiveProject(int $projectId): Response
    {
        return $this->updateProject($projectId, fn (Project $project) => $project->update(archivedAt: null));
    }

    #[Post('/projects/{projectId}/collapse')]
    public function collapseProject(int $projectId): Response
    {
        return $this->updateProject($projectId, fn (Project $project) => $project->update(collapsed: true));
    }

    #[Post('/projects/{projectId}/expand')]
    public function expandProject(int $projectId): Response
    {
        return $this->updateProject($projectId, fn (Project $project) => $project->update(collapsed: false));
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

    /**
     * @param Closure(Project): mixed $update Returning a response here, stops the update with that response.
     */
    private function updateProject(int $projectId, Closure $update): Response
    {
        /** @var Project|null $project */
        $project = Project::select()
            ->where('projects.id = ?', $projectId)
            ->where('projects.workspace_id IN (SELECT workspaces.id FROM workspaces WHERE workspaces.user_id = ?)', $this->user()->id->value)
            ->first();

        if (! $project) {
            return new NotFound();
        }

        $response = $update($project);

        return $response instanceof Response ? $response : new Ok();
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
        $workspaces = $this->workspaces();

        $workspace = $workspaceId === null
            ? $workspaces[0] ?? null
            : array_find($workspaces, fn (Workspace $workspace) => $workspace->id->value === $workspaceId);

        if ($workspaceId !== null && ! $workspace) {
            return new NotFound();
        }

        return $this->view(
            workspaces: $workspaces,
            workspace: $workspace,
            projects: array_filter($workspace->projects ?? [], fn (Project $project) => $project->archivedAt === null),
            filters: $filters,
            baseUri: $workspace ? uri([self::class, 'show'], workspaceId: $workspace->id) : uri([self::class, 'index']),
        );
    }

    /**
     * @param Workspace[] $workspaces
     * @param Project[] $projects
     * @param string[] $filters
     */
    private function view(array $workspaces, ?Workspace $workspace, array $projects, array $filters, string $baseUri, bool $isArchive = false): View
    {
        return view(
            'todo.view.php',
            workspaces: $workspaces,
            workspace: $workspace,
            isArchive: $isArchive,
            placeholder: $isArchive ? 'Nothing archived yet' : "# Project name\n\n- Todo 1\n- Todo 2",
            filters: $filters,
            filterLinks: $this->filterLinks($baseUri, $filters),
            baseUri: $baseUri,
            markdown: $this->markdown->render($projects, $filters),
            items: $this->markdown->items($projects, $filters),
            tags: $this->markdown->tags(...$workspaces),
        );
    }

    /**
     * @return Workspace[]
     */
    private function workspaces(): array
    {
        /** @var Workspace[] */
        return Workspace::select()
            ->with('projects.tasks')
            ->where('workspaces.user_id = ?', $this->user()->id->value)
            ->orderBy('workspaces.id ASC, projects.id ASC, projects_tasks.id ASC')
            ->all();
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
     * For every active filter, the URL that shows the page without it.
     *
     * @param string[] $filters
     * @return array<string, string>
     */
    private function filterLinks(string $baseUri, array $filters): array
    {
        $links = [];

        foreach ($filters as $filter) {
            $remaining = array_filter($filters, fn (string $other) => $other !== $filter);

            $links[$filter] = $this->filterUri($baseUri, $remaining);
        }

        return $links;
    }

    /**
     * @param string[] $filters
     */
    private function filterUri(string $baseUri, array $filters): string
    {
        return $filters === [] ? $baseUri : $baseUri . '?filter=' . rawurlencode(implode(' ', $filters));
    }

    private function user(): User
    {
        /** @var User */
        return $this->authenticator->current();
    }
}

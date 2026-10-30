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
use Tempest\Http\Responses\Json;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Responses\ServerError;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\Prefix;
use Tempest\View\View;

use function Tempest\Database\query;
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

    /**
     * Shows the workspace that was opened last, or the first one when there isn't one yet.
     * A first workspace is created when there are none yet.
     */
    #[Get('/')]
    public function index(Request $request): View
    {
        $workspaces = $this->workspaces();

        // Without a workspace, there's nowhere to save to, so there's always at least one
        if ($workspaces === []) {
            Workspace::create(user: $this->user(), name: 'Private');

            $workspaces = $this->workspaces();
        }

        $latestWorkspaceId = $this->user()->latestWorkspaceId;

        $workspace = array_find($workspaces, fn (Workspace $workspace) => $workspace->id->value === $latestWorkspaceId) ?? $workspaces[0] ?? null;

        return $this->view(
            workspaces: $workspaces,
            workspace: $workspace,
            groups: [
                ...array_filter([$workspace ? $this->markdown->upcoming($workspace) : null]),
                ...TodoGroup::forProjects(array_filter($workspace->projects ?? [], fn (Project $project) => $project->archivedAt === null)),
            ],
            request: $request,
            baseUri: uri([self::class, 'index']),
        );
    }

    /**
     * Switches to another workspace, which is remembered so that it's shown from now on.
     * The workspace is posted instead of being part of the URL, so that the URL of the todo page stays the same.
     * Optionally, only one of its projects is shown after switching.
     */
    #[Post('/workspace')]
    public function openWorkspace(Request $request): Response
    {
        $workspaceId = filter_var($request->get('workspace'), FILTER_VALIDATE_INT);

        /** @var Workspace|null $workspace */
        $workspace = $workspaceId === false
            ? null
            : Workspace::select()
                ->where('workspaces.id = ?', $workspaceId)
                ->where('workspaces.user_id = ?', $this->user()->id->value)
                ->first();

        if (! $workspace) {
            return new NotFound();
        }

        query('users')->update(latestWorkspaceId: $workspaceId)->where('id = ?', $this->user()->id->value)->execute();

        return new Redirect($this->filterUri(uri([self::class, 'index']), [], $this->project($request)));
    }

    /**
     * Open tasks with a date from all workspaces, below their project and sorted by date, latest first.
     */
    #[Get('/soon')]
    public function soon(Request $request): View
    {
        $workspaces = $this->workspaces();
        $project = $this->project($request);

        // When showing a single project, it can be opened with all of its tasks, in the workspace it belongs to
        $showAll = null;

        foreach ($workspaces as $workspace) {
            foreach ($workspace->projects as $candidate) {
                if ($showAll !== null || $project === null || $candidate->archivedAt !== null) {
                    continue;
                }

                if (mb_strtolower($candidate->name) === mb_strtolower($project)) {
                    $showAll = ['workspace' => $workspace->id->value, 'project' => $candidate->name];
                }
            }
        }

        return $this->view(
            workspaces: $workspaces,
            workspace: null,
            groups: $this->markdown->soon(...$workspaces),
            request: $request,
            baseUri: uri([self::class, 'soon']),
            mode: TodoMode::SOON,
            showAll: $showAll,
        );
    }

    /**
     * Archived projects of all workspaces, most recently archived first. They can be unarchived, but not edited.
     */
    #[Get('/archived')]
    public function archived(Request $request): View
    {
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
            groups: TodoGroup::forProjects($projects),
            request: $request,
            baseUri: uri([self::class, 'archived']),
            mode: TodoMode::ARCHIVED,
        );
    }

    /**
     * Creates a new workspace, which is opened right away.
     */
    #[Post('/workspaces')]
    public function createWorkspace(Request $request): Response
    {
        $name = $request->get('name');
        $name = is_string($name) ? mb_substr(trim($name), 0, 255) : '';

        if ($name === '') {
            return new Redirect(uri([self::class, 'index']));
        }

        $workspace = Workspace::create(user: $this->user(), name: $name);

        query('users')->update(latestWorkspaceId: $workspace->id->value)->where('id = ?', $this->user()->id->value)->execute();

        return new Redirect(uri([self::class, 'index']));
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
        return $this->updateProject($projectId, fn (Project $project) => $project->update(archivedAt: DateTime::now()));
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
     * Shows the given groups, optionally filtered by tags and a project, as requested.
     *
     * @param Workspace[] $workspaces
     * @param TodoGroup[] $groups
     * @param array{workspace: int|string, project: string}|null $showAll Where to see all tasks of the project that's shown
     */
    private function view(
        array $workspaces,
        ?Workspace $workspace,
        array $groups,
        Request $request,
        string $baseUri,
        TodoMode $mode = TodoMode::WORKSPACE,
        ?array $showAll = null,
    ): View {
        $filters = $this->filter($request);
        $project = $this->project($request);

        if ($project !== null) {
            $groups = array_values(array_filter($groups, fn (TodoGroup $group) => mb_strtolower($group->title) === mb_strtolower($project)));
        }

        return view(
            'todo.view.php',
            workspaces: $workspaces,
            workspace: $workspace,
            mode: $mode,
            title: $workspace->name ?? $mode->title(),
            placeholder: $mode->placeholder(),
            filters: $filters,
            filterLinks: $this->filterLinks($baseUri, $filters, $project),
            project: $project,
            showAll: $showAll,
            projectLink: $project !== null ? $this->filterUri($baseUri, $filters, null) : null,
            baseUri: $baseUri,
            markdown: $this->markdown->render($groups, $filters),
            items: $this->markdown->items($groups, $filters),
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
            ->orderBy('workspaces.id ASC, projects.position ASC, projects.id ASC, projects_tasks.id ASC')
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
     * Shows only the project with this name, for example `?project=Tempest`.
     */
    private function project(Request $request): ?string
    {
        $project = $request->get('project');

        if (! is_string($project) || trim($project) === '') {
            return null;
        }

        return trim($project);
    }

    /**
     * For every active tag filter, the URL that shows the page without it, but with the other filters.
     *
     * @param string[] $filters
     * @return array<string, string>
     */
    private function filterLinks(string $baseUri, array $filters, ?string $project): array
    {
        $links = [];

        foreach ($filters as $filter) {
            $remaining = array_filter($filters, fn (string $other) => $other !== $filter);

            $links[$filter] = $this->filterUri($baseUri, $remaining, $project);
        }

        return $links;
    }

    /**
     * @param string[] $filters
     */
    private function filterUri(string $baseUri, array $filters, ?string $project): string
    {
        $query = http_build_query(array_filter([
            'filter' => $filters === [] ? null : implode(' ', $filters),
            'project' => $project,
        ]), encoding_type: PHP_QUERY_RFC3986);

        return $query === '' ? $baseUri : $baseUri . '?' . $query;
    }

    private function user(): User
    {
        /** @var User */
        return $this->authenticator->current();
    }
}

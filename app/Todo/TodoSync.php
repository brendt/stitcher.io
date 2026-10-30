<?php

namespace App\Todo;

use Tempest\Database\Database;

use function Tempest\Database\query;

/**
 * Persists the editor's contents for a workspace.
 *
 * The editor sends its projects and tasks in order, together with the IDs of the ones it knows about.
 * Items without an ID are created, items with an ID are renamed or moved to another project when needed.
 * Only items the editor explicitly reports as deleted are removed,
 * so that saving a filtered view never touches what wasn't shown.
 * For the same reason, the order of tasks is only saved when the editor shows all of a project's tasks, which it reports as `ordered`,
 * and the order of projects only when it shows all projects, which it reports as `orderedProjects`.
 */
final readonly class TodoSync
{
    private const int MAX_NAME_LENGTH = 255;

    public function __construct(
        private Database $database,
    ) {}

    /**
     * @param array<mixed> $payload
     * @return array<string, int>|null The IDs of newly created items, keyed by the editor's key for them. `null` when saving failed.
     */
    public function sync(Workspace $workspace, array $payload): ?array
    {
        /** @var array<int, Project> $projects */
        $projects = [];

        /** @var array<int, Task> $tasks */
        $tasks = [];

        /** @var array<int, int> $taskProjects */
        $taskProjects = [];

        foreach ($workspace->projects as $project) {
            $projects[(int) $project->id->value] = $project;

            foreach ($project->tasks as $task) {
                $tasks[(int) $task->id->value] = $task;
                $taskProjects[(int) $task->id->value] = (int) $project->id->value;
            }
        }

        $createdIds = [];
        $presentProjects = [];
        $presentTasks = [];

        $isOrdered = ($payload['ordered'] ?? false) === true;
        $areProjectsOrdered = ($payload['orderedProjects'] ?? false) === true;
        $lastProjectPosition = max([0, ...array_map(fn (Project $project) => $project->position, $projects)]);

        /** @var array<int, int> $lastPositions */
        $lastPositions = [];

        foreach ($tasks as $id => $task) {
            $lastPositions[$taskProjects[$id]] = max($lastPositions[$taskProjects[$id]] ?? 0, $task->position);
        }

        $saved = $this->database->withinTransaction(function () use (
            $workspace,
            $payload,
            $projects,
            $tasks,
            $taskProjects,
            $isOrdered,
            $areProjectsOrdered,
            &$lastProjectPosition,
            &$lastPositions,
            &$createdIds,
            &$presentProjects,
            &$presentTasks,
        ): void {
            $projectPosition = 0;

            foreach ($this->list($payload['projects'] ?? null) as $projectData) {
                if (! is_array($projectData)) {
                    continue;
                }

                $name = $this->name($projectData['name'] ?? null);

                if ($name === null) {
                    continue;
                }

                $projectPosition++;
                $id = $this->id($projectData['id'] ?? null);
                $project = $id !== null ? $projects[$id] ?? null : null;

                if ($project && $id !== null) {
                    $changes = array_filter(
                        [
                            'name' => $project->name !== $name ? $name : null,
                            'position' => $areProjectsOrdered && $project->position !== $projectPosition ? $projectPosition : null,
                        ],
                        fn (mixed $value) => $value !== null,
                    );

                    if ($changes !== []) {
                        query('projects')->update(...$changes)->where('id = ?', $id)->execute();
                    }
                } else {
                    $project = Project::create(
                        workspace: $workspace,
                        name: $name,
                        // Without knowing the full order, new projects go after all others
                        position: $areProjectsOrdered ? $projectPosition : ++$lastProjectPosition,
                    );

                    $createdIds[$this->key($projectData['key'] ?? null)] = (int) $project->id->value;
                }

                $projectId = (int) $project->id->value;
                $presentProjects[$projectId] = true;

                $position = 0;

                foreach ($this->list($projectData['tasks'] ?? null) as $taskData) {
                    if (! is_array($taskData)) {
                        continue;
                    }

                    $kind = TaskKind::tryFrom(is_string($taskData['kind'] ?? null) ? $taskData['kind'] : '') ?? TaskKind::TASK;
                    $name = $this->name($taskData['name'] ?? null, keepIndentation: $kind === TaskKind::TEXT);

                    if ($name === null) {
                        continue;
                    }

                    $position++;
                    $id = $this->id($taskData['id'] ?? null);
                    $task = $id !== null ? $tasks[$id] ?? null : null;

                    if ($task && $id !== null) {
                        $changes = array_filter(
                            [
                                'name' => $task->name !== $name ? $name : null,
                                'project_id' => $taskProjects[$id] !== $projectId ? $projectId : null,
                                'kind' => $task->kind !== $kind ? $kind->value : null,
                                'position' => $isOrdered && $task->position !== $position ? $position : null,
                            ],
                            fn (mixed $value) => $value !== null,
                        );

                        if ($changes !== []) {
                            query('tasks')->update(...$changes)->where('id = ?', $id)->execute();
                        }
                    } else {
                        $lastPositions[$projectId] = ($lastPositions[$projectId] ?? 0) + 1;

                        $task = Task::create(
                            project: $project,
                            name: $name,
                            kind: $kind,
                            // Without knowing the full order, new tasks go after all others in their project
                            position: $isOrdered ? $position : $lastPositions[$projectId],
                        );

                        $createdIds[$this->key($taskData['key'] ?? null)] = (int) $task->id->value;
                    }

                    $presentTasks[(int) $task->id->value] = true;
                }
            }

            $deleted = is_array($payload['deleted'] ?? null) ? $payload['deleted'] : [];

            foreach ($this->list($deleted['tasks'] ?? null) as $id) {
                $id = $this->id($id);

                if ($id !== null && isset($tasks[$id]) && ! isset($presentTasks[$id])) {
                    $tasks[$id]->delete();
                }
            }

            foreach ($this->list($deleted['projects'] ?? null) as $id) {
                $id = $this->id($id);

                if ($id !== null && isset($projects[$id]) && ! isset($presentProjects[$id])) {
                    $projects[$id]->delete();
                }
            }
        });

        return $saved ? array_filter($createdIds, fn (int $_, string $key) => $key !== '', ARRAY_FILTER_USE_BOTH) : null;
    }

    /**
     * @return array<mixed>
     */
    private function list(mixed $value): array
    {
        return is_array($value) ? array_values($value) : [];
    }

    /**
     * Text keeps its indentation, so that it doesn't become a task or project when it's shown again.
     */
    private function name(mixed $value, bool $keepIndentation = false): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $name = mb_substr($keepIndentation ? rtrim($value) : trim($value), 0, self::MAX_NAME_LENGTH);

        return $name === '' ? null : $name;
    }

    private function id(mixed $value): ?int
    {
        return is_int($value) && $value > 0 ? $value : null;
    }

    private function key(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}

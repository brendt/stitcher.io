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

        $saved = $this->database->withinTransaction(function () use (
            $workspace,
            $payload,
            $projects,
            $tasks,
            $taskProjects,
            &$createdIds,
            &$presentProjects,
            &$presentTasks,
        ): void {
            foreach ($this->list($payload['projects'] ?? null) as $projectData) {
                if (! is_array($projectData)) {
                    continue;
                }

                $name = $this->name($projectData['name'] ?? null);

                if ($name === null) {
                    continue;
                }

                $id = $this->id($projectData['id'] ?? null);
                $project = $id !== null ? $projects[$id] ?? null : null;

                if ($project) {
                    if ($project->name !== $name) {
                        query('projects')->update(name: $name)->where('id = ?', $id)->execute();
                    }
                } else {
                    $project = Project::create(workspace: $workspace, name: $name);
                    $createdIds[$this->key($projectData['key'] ?? null)] = (int) $project->id->value;
                }

                $projectId = (int) $project->id->value;
                $presentProjects[$projectId] = true;

                foreach ($this->list($projectData['tasks'] ?? null) as $taskData) {
                    if (! is_array($taskData)) {
                        continue;
                    }

                    $name = $this->name($taskData['name'] ?? null);

                    if ($name === null) {
                        continue;
                    }

                    $id = $this->id($taskData['id'] ?? null);
                    $task = $id !== null ? $tasks[$id] ?? null : null;

                    if ($task && $id !== null) {
                        if ($task->name !== $name || $taskProjects[$id] !== $projectId) {
                            query('tasks')->update(name: $name, project_id: $projectId)->where('id = ?', $id)->execute();
                        }
                    } else {
                        $task = Task::create(project: $project, name: $name);
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

    private function name(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $name = mb_substr(trim($value), 0, self::MAX_NAME_LENGTH);

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

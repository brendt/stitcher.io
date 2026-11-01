<?php

namespace App\Todo;

use Tempest\Database\HasMany;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Virtual;
use Tempest\DateTime\DateTime;
use Tempest\Router\Bindable;

use function Tempest\Database\query;

final class Task implements Bindable
{
    use IsDatabaseModel;

    public function __construct(
        public Project $project,
        public string $name,
        public ?DateTime $completedAt = null,
        public TaskKind $kind = TaskKind::TASK,
        // Open tasks and text are shown in this order, which is the order they were saved in
        public int $position = 0,
        // A subtask is shown indented below the task it belongs to, which belongs to the same project
        public ?Task $parent = null,
    ) {}

    /** @var \App\Todo\Task[] */
    #[HasMany(ownerJoin: 'tasks.parent_id', relationJoin: 'tasks.id')]
    public array $children = [];

    #[Virtual]
    public bool $isText {
        get => $this->kind === TaskKind::TEXT;
    }

    /**
     * Links the given tasks to their parent and children among them, with a single query.
     * Tempest can't load these relations together with the tasks of a project yet, because the tasks table is aliased then.
     */
    public static function linkFamilies(Task ...$tasks): void
    {
        /** @var array<int, Task> $tasksById */
        $tasksById = [];

        foreach ($tasks as $task) {
            $task->parent = null;
            $task->children = [];
            $tasksById[(int) $task->id->value] = $task;
        }

        if ($tasksById === []) {
            return;
        }

        /** @var array<array{id: int, parent_id: int|null}> $rows */
        $rows = query('tasks')
            ->select('id', 'parent_id')
            ->whereIn('id', array_keys($tasksById))
            ->whereNotNull('parent_id')
            ->all();

        foreach ($rows as $row) {
            $task = $tasksById[(int) $row['id']] ?? null;
            $parent = $tasksById[(int) $row['parent_id']] ?? null;

            if ($task === null || $parent === null) {
                continue;
            }

            $task->parent = $parent;
            $parent->children[] = $task;
        }
    }
}

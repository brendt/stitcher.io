<?php

namespace App\Todo;

use Tempest\DateTime\FormatPattern;

/**
 * A title with tasks below it, as shown in the editor. Usually that's a project, but tasks can be grouped in other ways, like by date.
 */
final readonly class TodoGroup
{
    /**
     * @param Task[] $tasks Shown in the given order
     * @param array<int, string> $projectNames When given, tasks are shown together with the name of their project, keyed by task ID
     * @param bool $isVirtual A virtual group only shows tasks that belong to other projects, so it can't be edited or saved
     * @param array<int, non-negative-int> $depths When given, how deep tasks are indented as subtasks, keyed by task ID
     * @param 'light'|'dark'|null $shade For a group that isn't a project, the background it's shown with, if any
     */
    public function __construct(
        public string $title,
        public ?Project $project,
        public array $tasks,
        public array $projectNames = [],
        public bool $isVirtual = false,
        public array $depths = [],
        public ?string $shade = null,
    ) {}

    /**
     * Projects keep their order, also when they're completed.
     * Within a project, open tasks and text keep the order they were saved in, followed by completed tasks, most recently completed first.
     * Subtasks are shown right below the task they belong to, in the same order.
     *
     * @param Project[] $projects
     * @return self[]
     */
    public static function forProjects(array $projects): array
    {
        return array_values(array_map(
            function (Project $project) {
                $depths = [];
                $tasks = self::nested($project->tasks, $depths);

                return new self($project->name, $project, $tasks, depths: $depths);
            },
            $projects,
        ));
    }

    /**
     * Tasks whose parent belongs to another project are shown as tasks of their own.
     *
     * @param Task[] $tasks
     * @param array<int, non-negative-int> $depths Filled with how deep every task is indented, keyed by task ID
     * @return Task[]
     */
    private static function nested(array $tasks, array &$depths): array
    {
        $ids = array_flip(array_map(fn (Task $task) => (int) $task->id->value, $tasks));
        $roots = array_filter($tasks, fn (Task $task) => $task->parent === null || ! isset($ids[(int) $task->parent->id->value]));

        $nested = [];
        self::addNested($roots, $ids, 0, $nested, $depths);

        return $nested;
    }

    /**
     * @param Task[] $siblings
     * @param array<int, array-key> $ids The IDs of the tasks that are shown, as keys
     * @param non-negative-int $depth
     * @param Task[] $nested
     * @param array<int, non-negative-int> $depths
     */
    private static function addNested(array $siblings, array $ids, int $depth, array &$nested, array &$depths): void
    {
        $siblings = array_filter($siblings, fn (Task $task) => isset($ids[(int) $task->id->value]));

        foreach (self::completedLast($siblings) as $task) {
            $id = (int) $task->id->value;

            // Every task is shown once, even if its parents were to form a loop
            if (isset($depths[$id])) {
                continue;
            }

            $nested[] = $task;
            $depths[$id] = $depth;

            self::addNested($task->children, $ids, $depth + 1, $nested, $depths);
        }
    }

    /**
     * @param Task[] $tasks
     * @return Task[]
     */
    private static function completedLast(array $tasks): array
    {
        $open = array_filter($tasks, fn (Task $task) => $task->completedAt === null);
        $completed = array_filter($tasks, fn (Task $task) => $task->completedAt !== null);

        usort($open, fn (Task $a, Task $b) => [$a->position, $a->id->value] <=> [$b->position, $b->id->value]);

        usort(
            $completed,
            fn (Task $a, Task $b) => ($b->completedAt?->format(FormatPattern::SQL_DATE_TIME) ?? '') <=> ($a->completedAt?->format(FormatPattern::SQL_DATE_TIME) ?? ''),
        );

        return [...$open, ...$completed];
    }
}

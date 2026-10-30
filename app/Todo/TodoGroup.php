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
     */
    public function __construct(
        public string $title,
        public ?Project $project,
        public array $tasks,
    ) {}

    /**
     * Projects keep their order, also when they're completed.
     * Within a project, open tasks keep their order, followed by completed tasks, most recently completed first.
     *
     * @param Project[] $projects
     * @return self[]
     */
    public static function forProjects(array $projects): array
    {
        return array_values(array_map(
            fn (Project $project) => new self($project->name, $project, self::completedLast($project->tasks)),
            $projects,
        ));
    }

    /**
     * @param Task[] $tasks
     * @return Task[]
     */
    private static function completedLast(array $tasks): array
    {
        $open = array_filter($tasks, fn (Task $task) => $task->completedAt === null);
        $completed = array_filter($tasks, fn (Task $task) => $task->completedAt !== null);

        usort(
            $completed,
            fn (Task $a, Task $b) => ($b->completedAt?->format(FormatPattern::SQL_DATE_TIME) ?? '') <=> ($a->completedAt?->format(FormatPattern::SQL_DATE_TIME) ?? ''),
        );

        return [...$open, ...$completed];
    }
}

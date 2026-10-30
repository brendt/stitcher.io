<?php

namespace App\Todo;

use Generator;
use Tempest\DateTime\FormatPattern;

final readonly class TodoMarkdown
{
    /**
     * A hashtag or mention is a word within a task that starts with `#` or `@`.
     * Keep this in sync with `TAG` in todo.entrypoint.ts.
     */
    public const string TAG = '[#@][\\p{L}\\p{N}_-]+';

    public static function isTag(string $value): bool
    {
        return preg_match('/^' . self::TAG . '$/u', $value) === 1;
    }

    /**
     * @param Project[] $projects Rendered in the given order
     * @param string[] $filters Only tasks with all of these hashtags or mentions are included
     */
    public function render(array $projects, array $filters = []): string
    {
        $lines = [];

        foreach ($this->lines($projects, $filters) as [$text]) {
            $lines[] = $text;
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * All hashtags and mentions used in tasks, most used first. Tags that only differ in case are counted as one.
     *
     * @return string[]
     */
    public function tags(Workspace ...$workspaces): array
    {
        /** @var array<string, array{tag: string, count: int}> $tags */
        $tags = [];

        foreach ($workspaces as $workspace) {
            foreach ($workspace->projects as $project) {
                foreach ($project->tasks as $task) {
                    $matches = [];
                    preg_match_all('/(?:^|\\s)(' . self::TAG . ')/u', $task->name, $matches);

                    foreach ($matches[1] ?? [] as $tag) {
                        $key = mb_strtolower($tag);
                        $tags[$key] ??= ['tag' => $tag, 'count' => 0];
                        $tags[$key]['count']++;
                    }
                }
            }
        }

        uasort($tags, fn (array $a, array $b) => [$b['count'], $a['tag']] <=> [$a['count'], $b['tag']]);

        return array_values(array_column($tags, 'tag'));
    }

    /**
     * Maps line numbers (starting at 1) to the project or task they represent.
     *
     * @param Project[] $projects
     * @param string[] $filters Only tasks with all of these hashtags or mentions are included
     * @return array<int, array{type: 'project'|'task', id: int, completed: bool, collapsed?: bool}>
     */
    public function items(array $projects, array $filters = []): array
    {
        $items = [];
        $lineNumber = 0;

        foreach ($this->lines($projects, $filters) as [, $item]) {
            $lineNumber++;

            if ($item) {
                $items[$lineNumber] = $item;
            }
        }

        return $items;
    }

    /**
     * Projects keep their order, also when they're completed. Within a project, completed tasks go last.
     *
     * @param Project[] $projects
     * @param string[] $filters
     * @return Generator<array{0: string, 1: array{type: 'project'|'task', id: int, completed: bool, collapsed?: bool}|null}>
     */
    private function lines(array $projects, array $filters): Generator
    {
        $isFirstProject = true;

        foreach ($projects as $project) {
            $tasks = $this->completedLast($project->tasks);

            if ($filters !== []) {
                $tasks = array_filter($tasks, fn (Task $task) => array_all($filters, fn (string $filter) => $this->hasTag($task, $filter)));

                if ($tasks === []) {
                    continue;
                }
            }

            if (! $isFirstProject) {
                yield ['', null];
            }

            $isFirstProject = false;

            yield [
                "# {$project->name}",
                [
                    'type' => 'project',
                    'id' => (int) $project->id->value,
                    'completed' => $project->completedAt !== null,
                    'collapsed' => $project->collapsed,
                ],
            ];
            yield ['', null];

            foreach ($tasks as $task) {
                yield [
                    "- {$task->name}",
                    ['type' => 'task', 'id' => (int) $task->id->value, 'completed' => $task->completedAt !== null],
                ];
            }
        }
    }

    private function hasTag(Task $task, string $tag): bool
    {
        return preg_match('/(?:^|\\s)' . preg_quote($tag, '/') . '(?![\\p{L}\\p{N}_-])/iu', $task->name) === 1;
    }

    /**
     * Keeps open tasks in their original order, followed by completed tasks, most recently completed first.
     *
     * @param Task[] $tasks
     * @return Task[]
     */
    private function completedLast(array $tasks): array
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

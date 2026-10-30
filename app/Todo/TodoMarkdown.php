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

    public function render(Workspace $workspace, ?string $filter = null): string
    {
        $lines = [];

        foreach ($this->lines($workspace, $filter) as [$text]) {
            $lines[] = $text;
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Maps line numbers (starting at 1) to the project or task they represent.
     *
     * @return array<int, array{type: 'project'|'task', id: int, completed: bool}>
     */
    public function items(Workspace $workspace, ?string $filter = null): array
    {
        $items = [];
        $lineNumber = 0;

        foreach ($this->lines($workspace, $filter) as [, $item]) {
            $lineNumber++;

            if ($item) {
                $items[$lineNumber] = $item;
            }
        }

        return $items;
    }

    /**
     * @return Generator<array{0: string, 1: array{type: 'project'|'task', id: int, completed: bool}|null}>
     */
    private function lines(Workspace $workspace, ?string $filter): Generator
    {
        $isFirstProject = true;

        foreach ($this->completedLast($workspace->projects) as $project) {
            $tasks = $this->completedLast($project->tasks);

            if ($filter !== null) {
                $tasks = array_filter($tasks, fn (Task $task) => $this->hasTag($task, $filter));

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
                ['type' => 'project', 'id' => (int) $project->id->value, 'completed' => $project->completedAt !== null],
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
     * Keeps open items in their original order, followed by completed items, most recently completed first.
     *
     * @template T of Project|Task
     * @param T[] $items
     * @return T[]
     */
    private function completedLast(array $items): array
    {
        $open = array_filter($items, fn (Project|Task $item) => $item->completedAt === null);
        $completed = array_filter($items, fn (Project|Task $item) => $item->completedAt !== null);

        usort(
            $completed,
            fn (Project|Task $a, Project|Task $b) => (
                ($b->completedAt?->format(FormatPattern::SQL_DATE_TIME) ?? '') <=> ($a->completedAt?->format(FormatPattern::SQL_DATE_TIME) ?? '')
            ),
        );

        return [...$open, ...$completed];
    }
}

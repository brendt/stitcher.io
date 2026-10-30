<?php

namespace App\Todo;

use Generator;
use Tempest\DateTime\FormatPattern;

final readonly class TodoMarkdown
{
    public function render(Workspace $workspace): string
    {
        $lines = [];

        foreach ($this->lines($workspace) as [$text]) {
            $lines[] = $text;
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Maps line numbers (starting at 1) to the project or task they represent.
     *
     * @return array<int, array{type: 'project'|'task', id: int, completed: bool}>
     */
    public function items(Workspace $workspace): array
    {
        $items = [];
        $lineNumber = 0;

        foreach ($this->lines($workspace) as [, $item]) {
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
    private function lines(Workspace $workspace): Generator
    {
        $isFirstProject = true;

        foreach ($this->completedLast($workspace->projects) as $project) {
            if (! $isFirstProject) {
                yield ['', null];
            }

            $isFirstProject = false;

            yield [
                "# {$project->name}",
                ['type' => 'project', 'id' => (int) $project->id->value, 'completed' => $project->completedAt !== null],
            ];
            yield ['', null];

            foreach ($this->completedLast($project->tasks) as $task) {
                yield [
                    "- {$task->name}",
                    ['type' => 'task', 'id' => (int) $task->id->value, 'completed' => $task->completedAt !== null],
                ];
            }
        }
    }

    /**
     * Keeps open items in their original order, followed by completed items in the order they were completed.
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
                ($a->completedAt?->format(FormatPattern::SQL_DATE_TIME) ?? '') <=> ($b->completedAt?->format(FormatPattern::SQL_DATE_TIME) ?? '')
            ),
        );

        return [...$open, ...$completed];
    }
}

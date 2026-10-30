<?php

namespace App\Todo;

use Generator;

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
     * @return array<int, array{type: 'project'|'task', id: int}>
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
     * @return Generator<array{0: string, 1: array{type: 'project'|'task', id: int}|null}>
     */
    private function lines(Workspace $workspace): Generator
    {
        $isFirstProject = true;

        foreach ($workspace->projects as $project) {
            if ($project->completedAt) {
                continue;
            }

            if (! $isFirstProject) {
                yield ['', null];
            }

            $isFirstProject = false;

            yield ["# {$project->name}", ['type' => 'project', 'id' => (int) $project->id->value]];
            yield ['', null];

            foreach ($project->tasks as $task) {
                if ($task->completedAt) {
                    continue;
                }

                yield ["- {$task->name}", ['type' => 'task', 'id' => (int) $task->id->value]];
            }
        }
    }
}

<?php

namespace App\Todo;

final readonly class TodoMarkdown
{
    public function render(Workspace $workspace): string
    {
        $blocks = [];

        foreach ($workspace->projects as $project) {
            if ($project->completedAt) {
                continue;
            }

            $lines = ["# {$project->name}", ''];

            foreach ($project->tasks as $task) {
                if ($task->completedAt) {
                    continue;
                }

                $lines[] = "- {$task->name}";
            }

            $blocks[] = implode(PHP_EOL, $lines);
        }

        return implode(PHP_EOL . PHP_EOL, $blocks);
    }
}

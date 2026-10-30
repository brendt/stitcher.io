<?php

namespace App\Todo;

use Generator;
use Tempest\DateTime\DateTime;

final readonly class TodoMarkdown
{
    /**
     * A tag is a word within a task that can be filtered on:
     * a hashtag or mention starting with `#` or `@`, or a date formatted like `2026-10-30`.
     * Keep this in sync with `TAG` in todo.entrypoint.ts.
     */
    public const string TAG = '(?:[#@][\\p{L}\\p{N}_-]+|' . self::DATE . ')(?![\\p{L}\\p{N}_-])';

    private const string DATE = '\\d{4}-(?:0[1-9]|1[0-2])-(?:0[1-9]|[12]\\d|3[01])';

    public static function isTag(string $value): bool
    {
        return preg_match('/^' . self::TAG . '$/u', $value) === 1;
    }

    /**
     * @param TodoGroup[] $groups Rendered in the given order
     * @param string[] $filters Only tasks with all of these tags are included, together with the text of their projects
     */
    public function render(array $groups, array $filters = []): string
    {
        $lines = [];

        foreach ($this->lines($groups, $filters) as [$text]) {
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
     * @param TodoGroup[] $groups
     * @param string[] $filters Only tasks with all of these tags are included
     * @return array<int, array{type: 'project'|'task'|'text', id: int|null, completed: bool, collapsed?: bool, virtual?: bool}>
     */
    public function items(array $groups, array $filters = []): array
    {
        $items = [];
        $lineNumber = 0;

        foreach ($this->lines($groups, $filters) as [, $item]) {
            $lineNumber++;

            if ($item) {
                $items[$lineNumber] = $item;
            }
        }

        return $items;
    }

    /**
     * Open tasks with a date, below their project. Projects keep their usual order, their tasks are sorted by their first date, latest first.
     * Tasks of completed or archived projects are left out, those are done.
     *
     * @return TodoGroup[]
     */
    public function soon(Workspace ...$workspaces): array
    {
        /** @var array<int, array{project: Project, tasks: array<array{date: string, task: Task}>}> $projects */
        $projects = [];

        foreach ($workspaces as $workspace) {
            foreach ($workspace->projects as $project) {
                if ($project->completedAt !== null || $project->archivedAt !== null) {
                    continue;
                }

                foreach ($project->tasks as $task) {
                    $date = $this->dateOf($task);

                    if ($task->isText || $task->completedAt !== null || $date === null) {
                        continue;
                    }

                    $id = (int) $project->id->value;
                    $projects[$id] ??= ['project' => $project, 'tasks' => []];
                    $projects[$id]['tasks'][] = ['date' => $date, 'task' => $task];
                }
            }
        }

        $groups = [];

        foreach ($projects as ['project' => $project, 'tasks' => $tasks]) {
            usort($tasks, fn (array $a, array $b) => [$b['date'], $a['task']->id->value] <=> [$a['date'], $b['task']->id->value]);

            $groups[] = new TodoGroup($project->name, $project, array_column($tasks, 'task'));
        }

        return $groups;
    }

    /**
     * Tasks that are coming up, shown together at the top as a virtual project, with the name of their own project:
     * the ones due today, or else tomorrow, or else in the coming week. Without any of those, there's nothing to show.
     * Only open tasks are shown, completed ones are done already.
     */
    public function upcoming(Workspace $workspace): ?TodoGroup
    {
        $today = DateTime::now();

        foreach ([['Today', 0, 0], ['Tomorrow', 1, 1], ['Soon', 0, 7]] as [$title, $fromDays, $toDays]) {
            $from = $today->plusDays($fromDays)->format('yyyy-MM-dd');
            $to = $today->plusDays($toDays)->format('yyyy-MM-dd');

            /** @var array<array{task: Task, date: string}> $found */
            $found = [];
            $projectNames = [];

            foreach ($workspace->projects as $project) {
                if ($project->completedAt !== null || $project->archivedAt !== null) {
                    continue;
                }

                foreach ($project->tasks as $task) {
                    $dates = array_filter($this->datesOf($task), fn (string $date) => $date >= $from && $date <= $to);

                    if ($task->isText || $task->completedAt !== null || $dates === []) {
                        continue;
                    }

                    $found[] = ['task' => $task, 'date' => min($dates)];
                    $projectNames[(int) $task->id->value] = $project->name;
                }
            }

            if ($found === []) {
                continue;
            }

            // Latest first, like the Soon page. Within the same day, tasks keep the order of their projects.
            usort($found, fn (array $a, array $b) => $b['date'] <=> $a['date']);

            return new TodoGroup($title, null, array_column($found, 'task'), $projectNames, isVirtual: true);
        }

        return null;
    }

    /**
     * Groups are separated by two blank lines. A group that's a project can be completed and collapsed, other groups are just a title.
     * Blank lines within a group aren't saved, so one is added wherever tasks and text meet, to keep them apart.
     *
     * @param TodoGroup[] $groups
     * @param string[] $filters
     * @return Generator<array{0: string, 1: array{type: 'project'|'task'|'text', id: int|null, completed: bool, collapsed?: bool, virtual?: bool}|null}>
     */
    private function lines(array $groups, array $filters): Generator
    {
        $isFirstGroup = true;

        foreach ($groups as $group) {
            $tasks = $group->tasks;

            // Filtering shows the matching tasks, together with all text of their project to give them context
            if ($filters !== []) {
                $matches = fn (Task $task) => array_all($filters, fn (string $filter) => $this->hasTag($task, $filter));

                if (! array_any($tasks, $matches)) {
                    continue;
                }

                $tasks = array_filter($tasks, fn (Task $task) => $task->isText || $matches($task));
            }

            if (! $isFirstGroup) {
                yield ['', null];
                yield ['', null];
            }

            $isFirstGroup = false;

            yield [
                "# {$group->title}",
                match (true) {
                    $group->isVirtual => ['type' => 'project', 'id' => null, 'completed' => false, 'virtual' => true],
                    $group->project !== null => [
                        'type' => 'project',
                        'id' => (int) $group->project->id->value,
                        'completed' => $group->project->completedAt !== null,
                        'collapsed' => $group->project->collapsed,
                    ],
                    default => null,
                },
            ];

            $previous = null;

            foreach ($tasks as $task) {
                if ($previous !== null && $previous->isText !== $task->isText) {
                    yield ['', null];
                }

                $previous = $task;

                $projectName = $group->projectNames[(int) $task->id->value] ?? null;
                $name = $projectName !== null ? "{$projectName}: {$task->name}" : $task->name;

                $item = [
                    'type' => $task->isText ? 'text' : 'task',
                    'id' => (int) $task->id->value,
                    'completed' => $task->completedAt !== null,
                ];

                if ($group->isVirtual) {
                    $item['virtual'] = true;
                }

                yield [$task->isText ? $name : "- {$name}", $item];
            }
        }
    }

    /**
     * @return string[]
     */
    private function datesOf(Task $task): array
    {
        $matches = [];

        preg_match_all('/(?:^|\\s)(' . self::DATE . ')(?![\\p{L}\\p{N}_-])/u', $task->name, $matches);

        return $matches[1] ?? [];
    }

    private function dateOf(Task $task): ?string
    {
        $matches = [];

        return preg_match('/(?:^|\\s)(' . self::DATE . ')(?![\\p{L}\\p{N}_-])/u', $task->name, $matches) === 1 ? $matches[1] : null;
    }

    private function hasTag(Task $task, string $tag): bool
    {
        return preg_match('/(?:^|\\s)' . preg_quote($tag, '/') . '(?![\\p{L}\\p{N}_-])/iu', $task->name) === 1;
    }
}

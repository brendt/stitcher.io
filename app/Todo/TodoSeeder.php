<?php

namespace App\Todo;

use App\Support\Authentication\Role;
use App\Support\Authentication\User;
use Tempest\Database\DatabaseSeeder;
use Tempest\DateTime\DateTime;
use UnitEnum;

use function Tempest\env;

final class TodoSeeder implements DatabaseSeeder
{
    public function run(UnitEnum|string|null $database): void
    {
        // The todo app is only accessible to admins, so make sure there is one to seed todos for
        $user = User::select()
            ->where('role = ?', Role::ADMIN->name)
            ->first();

        $user ??= User::create(
            name: 'Brent',
            email: env('SEEDER_EMAIL', 'test@example.com'),
            role: Role::ADMIN,
        );

        // Dates are relative to when seeding, so that there are always some in the past and some coming up
        $date = fn (int $days) => DateTime::now()->plusDays($days)->format('yyyy-MM-dd');
        $lastWeek = $date(-7);
        $tomorrow = $date(1);
        $nextWeek = $date(7);

        $workspaces = [
            'Private' => [
                'House' => [
                    'Fix the leaking tap with @sam' => true,
                    "Paint the bedroom #weekend {$nextWeek}" => false,
                    'Clean out the garage #weekend' => false,
                    "Buy paint and brushes #shopping {$tomorrow}" => false,
                ],
                'Garden' => [
                    "Mow the lawn #weekend {$lastWeek}" => true,
                    'Plant tulip bulbs' => false,
                    'Get compost #shopping' => false,
                    'Borrow the hedge trimmer from @sam' => false,
                ],
                'Holiday' => [
                    'Book flights' => true,
                    'Book hotel with @alex' => true,
                    "Renew passport #urgent before {$lastWeek}" => true,
                ],
            ],
            'Work' => [
                'stitcher.io' => [
                    'Write blog post on PHP 8.6 #writing' => false,
                    'Update newsletter template' => true,
                    "Fix RSS feed dates #urgent {$tomorrow}" => false,
                    'Ask @alex to proofread the newsletter #writing' => false,
                ],
                'Tempest' => [
                    'Review open pull requests #urgent' => false,
                    "Tag new release with @jordan on {$nextWeek}" => false,
                    'Update database docs #writing' => true,
                    'Pair with @jordan on the router' => false,
                ],
                'Conference talk' => [
                    'Submit abstract #writing' => true,
                    'Prepare slides' => false,
                    "Send travel details to @alex #urgent {$tomorrow}" => false,
                ],
            ],
        ];

        foreach ($workspaces as $workspaceName => $projects) {
            $workspace = Workspace::create(
                user: $user,
                name: $workspaceName,
            );

            foreach ($projects as $projectName => $tasks) {
                $isProjectCompleted = ! in_array(false, $tasks, strict: true);

                $project = Project::create(
                    workspace: $workspace,
                    name: $projectName,
                    completedAt: $isProjectCompleted ? DateTime::now()->minusDays(random_int(1, 30)) : null,
                );

                foreach ($tasks as $taskName => $isCompleted) {
                    Task::create(
                        project: $project,
                        name: $taskName,
                        completedAt: $isCompleted ? DateTime::now()->minusDays(random_int(1, 30)) : null,
                    );
                }
            }
        }
    }
}

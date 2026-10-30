<?php

namespace App\Todo;

use App\Support\Authentication\Role;
use App\Support\Authentication\User;
use RuntimeException;
use Tempest\Database\DatabaseSeeder;
use Tempest\DateTime\DateTime;
use UnitEnum;

final class TodoSeeder implements DatabaseSeeder
{
    public function run(UnitEnum|string|null $database): void
    {
        $user = User::select()
            ->where('role = ?', Role::ADMIN->name)
            ->first();

        if (! $user) {
            throw new RuntimeException('No admin user found to seed todos for, run AppSeeder first');
        }

        $workspaces = [
            'Private' => [
                'House' => [
                    'Fix the leaking tap with @sam' => true,
                    'Paint the bedroom #weekend' => false,
                    'Clean out the garage #weekend' => false,
                    'Buy paint and brushes #shopping' => false,
                ],
                'Garden' => [
                    'Mow the lawn #weekend' => true,
                    'Plant tulip bulbs' => false,
                    'Get compost #shopping' => false,
                    'Borrow the hedge trimmer from @sam' => false,
                ],
                'Holiday' => [
                    'Book flights' => true,
                    'Book hotel with @alex' => true,
                    'Renew passport #urgent' => true,
                ],
            ],
            'Work' => [
                'stitcher.io' => [
                    'Write blog post on PHP 8.6 #writing' => false,
                    'Update newsletter template' => true,
                    'Fix RSS feed dates #urgent' => false,
                    'Ask @alex to proofread the newsletter #writing' => false,
                ],
                'Tempest' => [
                    'Review open pull requests #urgent' => false,
                    'Tag new release with @jordan' => false,
                    'Update database docs #writing' => true,
                    'Pair with @jordan on the router' => false,
                ],
                'Conference talk' => [
                    'Submit abstract #writing' => true,
                    'Prepare slides' => false,
                    'Send travel details to @alex #urgent' => false,
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

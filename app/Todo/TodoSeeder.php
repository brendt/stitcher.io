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
                    'Fix the leaking tap' => true,
                    'Paint the bedroom' => false,
                    'Clean out the garage' => false,
                ],
                'Garden' => [
                    'Mow the lawn' => true,
                    'Plant tulip bulbs' => false,
                ],
                'Holiday' => [
                    'Book flights' => true,
                    'Book hotel' => true,
                    'Renew passport' => true,
                ],
            ],
            'Work' => [
                'stitcher.io' => [
                    'Write blog post on PHP 8.6' => false,
                    'Update newsletter template' => true,
                    'Fix RSS feed dates' => false,
                ],
                'Tempest' => [
                    'Review open pull requests' => false,
                    'Tag new release' => false,
                    'Update database docs' => true,
                ],
                'Conference talk' => [
                    'Submit abstract' => true,
                    'Prepare slides' => false,
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
